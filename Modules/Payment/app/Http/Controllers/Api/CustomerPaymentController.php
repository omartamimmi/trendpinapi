<?php

namespace Modules\Payment\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\app\Models\TokenizedCard;
use Modules\Payment\app\Models\PaymentMethodSetting;
use Modules\Payment\app\Models\CliqPaymentRequest;
use Modules\Payment\Services\BankDiscountService;
use Modules\Payment\Services\PaymentService;
use Modules\BankOffer\app\Models\Bank;

class CustomerPaymentController extends Controller
{
    public function __construct(
        private readonly BankDiscountService $discountService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * POST /api/v1/customer/qr-sessions/{code}/scan
     * Scan and verify QR code
     */
    public function scan(string $code): JsonResponse
    {
        $session = QrPaymentSession::where('session_code', $code)
            ->with(['brand', 'branch', 'retailer'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_code',
                'message' => 'Invalid QR code',
            ], 404);
        }

        if ($session->isExpired()) {
            return response()->json([
                'success' => false,
                'error' => 'expired',
                'message' => 'This payment session has expired',
            ], 410);
        }

        if (!$session->canBeScanned()) {
            return response()->json([
                'success' => false,
                'error' => 'already_used',
                'message' => 'This QR code has already been used',
                'status' => $session->status,
            ], 409);
        }

        // Mark as scanned
        $session->markAsScanned(auth()->id());

        // Broadcast scan event (will be implemented later)
        // broadcast(new QrSessionScanned($session))->toOthers();

        // Get available bank offers for this branch
        $availableOffers = $this->discountService->getAvailableOffers(
            $session->branch_id,
            $session->amount
        );

        // Get user's saved cards with offer info
        $savedCards = $this->getUserCardsWithOffers(
            auth()->id(),
            $session->branch_id,
            $session->amount
        );

        // Get enabled payment methods
        $enabledMethods = $this->getEnabledPaymentMethods();

        return response()->json([
            'success' => true,
            'data' => [
                'session_code' => $session->session_code,
                'amount' => (float) $session->amount,
                'currency' => $session->currency,
                'description' => $session->description,
                'retailer' => [
                    'id' => $session->brand?->id ?? $session->retailer_id,
                    'name' => $session->brand?->name ?? $session->retailer?->business_name,
                    'name_ar' => $session->brand?->name_ar,
                    'logo' => $session->brand?->logo_url,
                ],
                'branch' => [
                    'id' => $session->branch->id,
                    'name' => $session->branch->name,
                    'name_ar' => $session->branch->name_ar ?? null,
                    'location' => $session->branch->location ?? null,
                    'address' => $session->branch->address ?? null,
                ],
                'status' => $session->status,
                'expires_at' => $session->expires_at->toIso8601String(),
                'expires_in_seconds' => now()->diffInSeconds($session->expires_at),
                'available_offers' => $availableOffers,
                'saved_cards' => $savedCards,
                'enabled_payment_methods' => $enabledMethods,
            ],
        ]);
    }

    /**
     * POST /api/v1/customer/qr-sessions/{code}/calculate-discount
     * Calculate discount for selected card
     */
    public function calculateDiscount(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'card_bin' => 'required_without:tokenized_card_id|string|min:6|max:8',
            'tokenized_card_id' => 'required_without:card_bin|integer|exists:tokenized_cards,id',
        ]);

        $session = QrPaymentSession::where('session_code', $code)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        if (!$session->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be paid',
                'status' => $session->status,
            ], 400);
        }

        // Get card BIN
        $cardBin = $request->card_bin;
        if ($request->tokenized_card_id) {
            $card = TokenizedCard::where('id', $request->tokenized_card_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found',
                ], 404);
            }

            $cardBin = $card->bin_prefix;
        }

        // Calculate discount
        $discount = $this->discountService->calculateDiscount(
            amount: $session->amount,
            cardBin: $cardBin,
            branchId: $session->branch_id,
            userId: auth()->id(),
        );

        return response()->json([
            'success' => true,
            'data' => $discount,
        ]);
    }

    /**
     * POST /api/v1/customer/qr-sessions/{code}/pay
     * Process payment with new card (3DS redirect)
     */
    public function pay(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'gateway' => 'nullable|string|in:tap,hyperpay,paytabs',
            'card_bin' => 'required|string|min:6',
            'save_card' => 'nullable|boolean',
            'card_nickname' => 'nullable|string|max:50',
            'redirect_url' => 'required|string',
        ]);

        $session = QrPaymentSession::where('session_code', $code)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or not scanned by you',
            ], 404);
        }

        if (!$session->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be paid',
                'status' => $session->status,
            ], 400);
        }

        // Check if card payments are enabled
        if (!PaymentMethodSetting::isMethodEnabled('card')) {
            return response()->json([
                'success' => false,
                'message' => 'Card payments are currently disabled',
            ], 503);
        }

        try {
            // Calculate discount
            $discount = $this->discountService->calculateDiscount(
                amount: $session->amount,
                cardBin: $request->card_bin,
                branchId: $session->branch_id,
                userId: auth()->id(),
            );

            // Update session with discount info
            $session->markAsProcessing(
                discountAmount: $discount['discount_amount'],
                bankOfferId: $discount['bank_offer_id'],
                gateway: $request->gateway ?? config('payment.default_gateway', 'tap'),
                paymentMethod: 'card',
            );

            // Initiate payment via gateway
            $paymentResult = $this->paymentService->initiateQrPayment(
                session: $session,
                user: auth()->user(),
                gateway: $request->gateway ?? config('payment.default_gateway', 'tap'),
                saveCard: $request->save_card ?? false,
                cardNickname: $request->card_nickname,
                redirectUrl: $request->redirect_url,
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'session_code' => $session->session_code,
                    'payment_id' => $paymentResult['payment_id'],
                    'status' => 'processing',
                    'original_amount' => (float) $session->original_amount,
                    'discount_amount' => (float) $session->discount_amount,
                    'final_amount' => (float) $session->final_amount,
                    'redirect_url' => $paymentResult['redirect_url'],
                    'requires_redirect' => !empty($paymentResult['redirect_url']),
                    'bank' => $discount['bank'],
                    'offer' => $discount['offer'],
                ],
            ]);
        } catch (\Exception $e) {
            // Revert session status
            $session->update(['status' => 'scanned']);

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/customer/qr-sessions/{code}/pay-with-saved-card
     * Process payment with tokenized card (one-tap)
     */
    public function payWithSavedCard(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'tokenized_card_id' => 'required|integer|exists:tokenized_cards,id',
        ]);

        $session = QrPaymentSession::where('session_code', $code)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or not scanned by you',
            ], 404);
        }

        if (!$session->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be paid',
                'status' => $session->status,
            ], 400);
        }

        // Get saved card
        $card = TokenizedCard::where('id', $request->tokenized_card_id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found or inactive',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Calculate discount
            $discount = $this->discountService->calculateDiscount(
                amount: $session->amount,
                cardBin: $card->bin_prefix,
                branchId: $session->branch_id,
                userId: auth()->id(),
            );

            // Update session
            $session->markAsProcessing(
                discountAmount: $discount['discount_amount'],
                bankOfferId: $discount['bank_offer_id'],
                gateway: $card->gateway,
                paymentMethod: 'card',
            );

            // Charge the token
            $paymentResult = $this->paymentService->chargeTokenForQrPayment(
                session: $session,
                card: $card,
                user: auth()->user(),
            );

            if ($paymentResult['success']) {
                // Mark session as completed
                $session->markAsCompleted(
                    paymentId: $paymentResult['payment_id'],
                    transactionId: $paymentResult['transaction_id'],
                );

                // Broadcast completion event
                // broadcast(new QrSessionCompleted($session))->toOthers();

                // Record bank offer redemption
                if ($session->bank_offer_id) {
                    $this->discountService->recordRedemption(
                        bankOfferId: $session->bank_offer_id,
                        userId: auth()->id(),
                        branchId: $session->branch_id,
                        originalAmount: $session->original_amount,
                        discountApplied: $session->discount_amount,
                    );
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'session_code' => $session->session_code,
                        'payment_id' => $paymentResult['payment_id'],
                        'status' => 'completed',
                        'original_amount' => (float) $session->original_amount,
                        'discount_amount' => (float) $session->discount_amount,
                        'final_amount' => (float) $session->final_amount,
                        'transaction_id' => $paymentResult['transaction_id'],
                        'bank' => $discount['bank'],
                        'offer' => $discount['offer'],
                        'card' => [
                            'last_four' => $card->card_last_four,
                            'brand' => $card->card_brand,
                        ],
                        'receipt' => $this->generateReceipt($session, $card, $discount),
                    ],
                ]);
            }

            DB::rollBack();

            // Revert session status
            $session->update(['status' => 'scanned']);

            return response()->json([
                'success' => false,
                'message' => $paymentResult['error_message'] ?? 'Payment failed',
                'error_code' => $paymentResult['error_code'] ?? null,
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            $session->update(['status' => 'scanned']);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
            ], 500);
        }
    }

    /**
     * POST /api/v1/customer/qr-sessions/{code}/pay-with-wallet
     * Pay with Apple Pay or Google Pay (Auth + Capture flow)
     */
    public function payWithWallet(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'wallet_type' => 'required|in:apple_pay,google_pay',
            'payment_token' => 'required|string',
        ]);

        $session = QrPaymentSession::where('session_code', $code)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        if (!$session->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be paid',
            ], 400);
        }

        // Check if wallet payments are enabled
        if (!PaymentMethodSetting::isMethodEnabled($request->wallet_type)) {
            return response()->json([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $request->wallet_type)) . ' is currently disabled',
            ], 503);
        }

        try {
            DB::beginTransaction();

            // Process wallet payment (authorize + capture)
            $paymentResult = $this->paymentService->processWalletPayment(
                session: $session,
                user: auth()->user(),
                walletType: $request->wallet_type,
                paymentToken: $request->payment_token,
            );

            if ($paymentResult['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'session_code' => $session->refresh()->session_code,
                        'payment_id' => $paymentResult['payment_id'],
                        'status' => 'completed',
                        'original_amount' => (float) $session->original_amount,
                        'authorized_amount' => (float) $session->original_amount,
                        'discount_amount' => (float) $session->discount_amount,
                        'final_amount' => (float) $session->final_amount,
                        'captured_amount' => (float) $session->final_amount,
                        'transaction_id' => $paymentResult['transaction_id'],
                        'payment_method' => $request->wallet_type,
                        'bank' => $paymentResult['bank'] ?? null,
                        'card' => $paymentResult['card'] ?? null,
                        'message' => $paymentResult['message'] ?? null,
                        'message_ar' => $paymentResult['message_ar'] ?? null,
                        'receipt' => $this->generateWalletReceipt($session, $request->wallet_type, $paymentResult),
                    ],
                ]);
            }

            DB::rollBack();
            $session->update(['status' => 'scanned']);

            return response()->json([
                'success' => false,
                'message' => $paymentResult['error_message'] ?? 'Wallet payment failed',
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            $session->update(['status' => 'scanned']);

            return response()->json([
                'success' => false,
                'message' => 'Wallet payment processing failed',
            ], 500);
        }
    }

    /**
     * POST /api/v1/customer/qr-sessions/{code}/pay-with-cliq
     * Initiate CliQ payment
     */
    public function payWithCliq(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'bank_id' => 'required|integer|exists:banks,id',
            'cliq_alias' => 'nullable|string|max:50',
        ]);

        $session = QrPaymentSession::where('session_code', $code)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        if (!$session->canBePaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be paid',
            ], 400);
        }

        // Check if CliQ is enabled
        if (!PaymentMethodSetting::isMethodEnabled('cliq')) {
            return response()->json([
                'success' => false,
                'message' => 'CliQ payments are currently disabled',
            ], 503);
        }

        $bank = Bank::findOrFail($request->bank_id);

        // Calculate discount (we know the bank since user selected it)
        $discount = $this->discountService->calculateDiscountByBank(
            amount: $session->amount,
            bankId: $bank->id,
            branchId: $session->branch_id,
            userId: auth()->id(),
        );

        // Generate CliQ request ID
        $cliqRequestId = 'CLIQ-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Update session with pending CliQ payment
        $session->update([
            'status' => 'processing',
            'original_amount' => $session->amount,
            'discount_amount' => $discount['discount_amount'],
            'final_amount' => $discount['final_amount'],
            'bank_offer_id' => $discount['bank_offer_id'],
            'gateway' => 'cliq',
            'payment_method' => 'cliq',
            'gateway_transaction_id' => $cliqRequestId,
        ]);

        // Create CliQ payment request
        $cliqRequest = CliqPaymentRequest::create([
            'qr_session_id' => $session->id,
            'request_id' => $cliqRequestId,
            'amount' => $discount['final_amount'],
            'currency' => 'JOD',
            'sender_bank_id' => $bank->id,
            'sender_alias' => $request->cliq_alias,
            'receiver_alias' => config('payment.gateways.cliq.merchant_alias'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Generate deep link to bank app
        $deepLinks = $this->generateBankDeepLink($bank, $cliqRequest, $session);

        return response()->json([
            'success' => true,
            'data' => [
                'session_code' => $session->session_code,
                'cliq_request_id' => $cliqRequestId,
                'status' => 'pending_bank_confirmation',
                'original_amount' => (float) $session->original_amount,
                'discount_amount' => (float) $discount['discount_amount'],
                'final_amount' => (float) $discount['final_amount'],
                'bank' => [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'name_ar' => $bank->name_ar,
                    'logo' => $bank->logo?->url,
                ],
                'offer' => $discount['offer'],
                'deep_link' => $deepLinks['deep_link'],
                'universal_link' => $deepLinks['universal_link'],
                'fallback_url' => $deepLinks['fallback_url'],
                'instructions' => "Complete payment in your {$bank->name} app",
                'instructions_ar' => "أكمل الدفع من خلال تطبيق {$bank->name_ar}",
                'expires_at' => $cliqRequest->expires_at->toIso8601String(),
                'expires_in_seconds' => now()->diffInSeconds($cliqRequest->expires_at),
            ],
        ]);
    }

    /**
     * GET /api/v1/customer/cards
     * List user's saved cards
     */
    public function listCards(): JsonResponse
    {
        $cards = TokenizedCard::where('user_id', auth()->id())
            ->where('is_active', true)
            ->with(['bank.logo'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cards->map(fn($card) => [
                'id' => $card->id,
                'last_four' => $card->card_last_four,
                'brand' => $card->card_brand,
                'expiry' => $card->card_expiry_month . '/' . $card->card_expiry_year,
                'nickname' => $card->nickname,
                'is_default' => $card->is_default,
                'wallet_type' => $card->wallet_type,
                'bank' => $card->bank ? [
                    'id' => $card->bank->id,
                    'name' => $card->bank->name,
                    'name_ar' => $card->bank->name_ar,
                    'logo' => $card->bank->logo?->url,
                ] : null,
                'created_at' => $card->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/v1/customer/cards
     * Save a new card
     */
    public function saveCard(Request $request): JsonResponse
    {
        $request->validate([
            'gateway' => 'nullable|string|in:tap,hyperpay,paytabs',
            'card_number' => 'required|string|min:13|max:19',
            'exp_month' => 'required|string|size:2',
            'exp_year' => 'required|string|min:2|max:4',
            'cvv' => 'required|string|min:3|max:4',
            'cardholder_name' => 'required|string|max:100',
            'nickname' => 'nullable|string|max:50',
            'set_default' => 'nullable|boolean',
        ]);

        try {
            $card = $this->paymentService->saveCard(
                user: auth()->user(),
                cardData: [
                    'card_number' => $request->card_number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvv' => $request->cvv,
                    'cardholder_name' => $request->cardholder_name,
                ],
                gateway: $request->gateway ?? config('payment.default_gateway', 'tap'),
                nickname: $request->nickname,
                setDefault: $request->set_default ?? false,
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $card->id,
                    'last_four' => $card->card_last_four,
                    'brand' => $card->card_brand,
                    'expiry' => $card->card_expiry_month . '/' . $card->card_expiry_year,
                    'nickname' => $card->nickname,
                    'is_default' => $card->is_default,
                    'bank' => $card->bank ? [
                        'id' => $card->bank->id,
                        'name' => $card->bank->name,
                        'logo' => $card->bank->logo?->url,
                    ] : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save card: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * DELETE /api/v1/customer/cards/{id}
     * Delete a saved card
     */
    public function deleteCard(int $id): JsonResponse
    {
        $card = TokenizedCard::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        try {
            $this->paymentService->deleteCard($card);

            return response()->json([
                'success' => true,
                'message' => 'Card deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete card',
            ], 500);
        }
    }

    /**
     * POST /api/v1/customer/cards/{id}/set-default
     * Set a card as default
     */
    public function setDefaultCard(int $id): JsonResponse
    {
        $card = TokenizedCard::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        // Remove default from other cards
        TokenizedCard::where('user_id', auth()->id())
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        // Set this card as default
        $card->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default card updated',
            'data' => [
                'id' => $card->id,
                'is_default' => true,
            ],
        ]);
    }

    /**
     * GET /api/v1/customer/payments
     * Get payment history
     */
    public function paymentHistory(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:completed,failed,refunded',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $payments = $this->paymentService->getUserPayments(
            user: auth()->user(),
            filters: $request->only(['status', 'date_from', 'date_to']),
            perPage: $request->per_page ?? 20,
        );

        return response()->json([
            'success' => true,
            'data' => $payments->map(fn($payment) => [
                'id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => (float) $payment->amount,
                'original_amount' => (float) $payment->original_amount,
                'discount_amount' => (float) $payment->discount_amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'retailer' => [
                    'name' => $payment->brand?->name ?? $payment->retailer_name,
                    'logo' => $payment->brand?->logo_url,
                ],
                'branch' => [
                    'name' => $payment->branch?->name ?? $payment->branch_name,
                ],
                'bank_offer' => $payment->bankOffer ? [
                    'bank_name' => $payment->bankOffer->bank?->name,
                    'offer_display' => $payment->bankOffer->discount_display,
                ] : null,
                'card' => $payment->card_last_four ? [
                    'last_four' => $payment->card_last_four,
                    'brand' => $payment->card_brand,
                ] : null,
                'created_at' => $payment->created_at->toIso8601String(),
                'completed_at' => $payment->completed_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/customer/payments/{id}
     * Get payment details
     */
    public function paymentDetails(int $id): JsonResponse
    {
        $payment = $this->paymentService->getPayment($id, auth()->user());

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'reference' => $payment->reference,
                'transaction_id' => $payment->gateway_transaction_id,
                'amount' => (float) $payment->amount,
                'original_amount' => (float) $payment->original_amount,
                'discount_amount' => (float) $payment->discount_amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'gateway' => $payment->gateway,
                'payment_method' => $payment->payment_method,
                'retailer' => [
                    'id' => $payment->brand_id,
                    'name' => $payment->brand?->name ?? $payment->retailer_name,
                    'logo' => $payment->brand?->logo_url,
                ],
                'branch' => [
                    'id' => $payment->branch_id,
                    'name' => $payment->branch?->name ?? $payment->branch_name,
                    'location' => $payment->branch?->location,
                ],
                'bank_offer' => $payment->bankOffer ? [
                    'id' => $payment->bank_offer_id,
                    'bank_name' => $payment->bankOffer->bank?->name,
                    'bank_logo' => $payment->bankOffer->bank?->logo?->url,
                    'offer_title' => $payment->bankOffer->title,
                    'offer_display' => $payment->bankOffer->discount_display,
                ] : null,
                'card' => $payment->card_last_four ? [
                    'last_four' => $payment->card_last_four,
                    'brand' => $payment->card_brand,
                ] : null,
                'created_at' => $payment->created_at->toIso8601String(),
                'completed_at' => $payment->completed_at?->toIso8601String(),
                'receipt_url' => route('customer.payment.receipt', $payment->id),
            ],
        ]);
    }

    /**
     * Get user's saved cards with offer information
     */
    private function getUserCardsWithOffers(int $userId, int $branchId, float $amount): array
    {
        $cards = TokenizedCard::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['bank.logo'])
            ->get();

        return $cards->map(function ($card) use ($branchId, $amount) {
            $discount = $this->discountService->calculateDiscount(
                amount: $amount,
                cardBin: $card->bin_prefix,
                branchId: $branchId,
            );

            return [
                'id' => $card->id,
                'last_four' => $card->card_last_four,
                'brand' => $card->card_brand,
                'nickname' => $card->nickname,
                'is_default' => $card->is_default,
                'wallet_type' => $card->wallet_type,
                'bank' => $card->bank ? [
                    'id' => $card->bank->id,
                    'name' => $card->bank->name,
                    'name_ar' => $card->bank->name_ar,
                    'logo' => $card->bank->logo?->url,
                ] : null,
                'has_active_offer' => $discount['has_discount'],
                'offer_display' => $discount['offer']['display'] ?? null,
                'potential_savings' => $discount['discount_amount'],
                'final_amount' => $discount['final_amount'],
            ];
        })->toArray();
    }

    /**
     * Get enabled payment methods
     */
    private function getEnabledPaymentMethods(): array
    {
        return [
            'card' => PaymentMethodSetting::isMethodEnabled('card'),
            'apple_pay' => PaymentMethodSetting::isMethodEnabled('apple_pay'),
            'google_pay' => PaymentMethodSetting::isMethodEnabled('google_pay'),
            'cliq' => PaymentMethodSetting::isMethodEnabled('cliq'),
        ];
    }

    /**
     * Generate bank deep link for CliQ
     */
    private function generateBankDeepLink(Bank $bank, CliqPaymentRequest $request, QrPaymentSession $session): array
    {
        // Bank deep link schemes (would be stored in database)
        $bankSchemes = [
            'arab_bank' => ['scheme' => 'arabbank', 'host' => 'pay'],
            'jordan_islamic' => ['scheme' => 'jib', 'host' => 'pay'],
            'housing_bank' => ['scheme' => 'hbtf', 'host' => 'transfer'],
            'cairo_amman' => ['scheme' => 'cab', 'host' => 'cliq'],
            'bank_of_jordan' => ['scheme' => 'boj', 'host' => 'cliq'],
            'capital_bank' => ['scheme' => 'capitalbank', 'host' => 'pay'],
        ];

        $config = $bankSchemes[$bank->slug ?? ''] ?? null;

        $params = http_build_query([
            'amount' => $request->amount,
            'ref' => $request->request_id,
            'merchant' => 'TrendPin',
            'alias' => config('payment.gateways.cliq.merchant_alias'),
            'desc' => $session->description ?? 'TrendPin Payment',
        ]);

        $baseUrl = config('app.url');

        return [
            'deep_link' => $config
                ? "{$config['scheme']}://{$config['host']}?{$params}"
                : null,
            'universal_link' => "{$baseUrl}/cliq/{$request->request_id}",
            'fallback_url' => "{$baseUrl}/cliq/{$request->request_id}/fallback",
        ];
    }

    /**
     * Generate receipt for card payment
     */
    private function generateReceipt(QrPaymentSession $session, TokenizedCard $card, array $discount): array
    {
        return [
            'retailer' => ($session->brand?->name ?? '') . ' - ' . ($session->branch?->name ?? ''),
            'date' => now()->format('Y-m-d H:i:s'),
            'amount_paid' => (float) $session->final_amount,
            'original_amount' => (float) $session->original_amount,
            'discount_applied' => (float) $session->discount_amount,
            'bank_offer' => $discount['offer']['display'] ?? null,
            'bank_name' => $discount['bank']['name'] ?? null,
            'card' => '**** ' . $card->card_last_four,
            'card_brand' => $card->card_brand,
            'reference' => $session->session_code,
        ];
    }

    /**
     * Generate receipt for wallet payment
     */
    private function generateWalletReceipt(QrPaymentSession $session, string $walletType, array $paymentResult): array
    {
        return [
            'retailer' => ($session->brand?->name ?? '') . ' - ' . ($session->branch?->name ?? ''),
            'date' => now()->format('Y-m-d H:i:s'),
            'amount_paid' => (float) $session->final_amount,
            'original_amount' => (float) $session->original_amount,
            'discount_applied' => (float) $session->discount_amount,
            'bank_offer' => $paymentResult['offer']['display'] ?? null,
            'bank_name' => $paymentResult['bank']['name'] ?? null,
            'payment_method' => $walletType === 'apple_pay' ? 'Apple Pay' : 'Google Pay',
            'card' => isset($paymentResult['card']['last_four']) ? '**** ' . $paymentResult['card']['last_four'] : null,
            'reference' => $session->session_code,
        ];
    }
}
