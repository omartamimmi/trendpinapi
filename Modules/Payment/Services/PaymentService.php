<?php

namespace Modules\Payment\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Payment\app\DTO\PaymentRequestDTO;
use Modules\Payment\app\DTO\PaymentResponseDTO;
use Modules\Payment\app\Models\PaymentMethodSetting;
use Modules\Payment\app\Models\PaymentSetting;
use Modules\Payment\app\Models\PaymentTransaction;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\app\Models\TokenizedCard;
use Modules\Payment\Services\Contracts\PaymentGatewayInterface;
use Modules\Payment\Services\Gateways\TapPaymentsGateway;

class PaymentService
{
    private BankDiscountService $discountService;

    public function __construct(BankDiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Get available payment gateway
     */
    public function getGateway(?string $gatewayName = null): PaymentGatewayInterface
    {
        $gatewayName = $gatewayName ?? config('payment.default_gateway', 'tap');

        return match ($gatewayName) {
            'tap' => new TapPaymentsGateway(),
            // 'hyperpay' => new HyperPayGateway(),
            // 'paytabs' => new PayTabsGateway(),
            default => new TapPaymentsGateway(),
        };
    }

    /**
     * Initiate QR payment with new card
     */
    public function initiateQrPayment(
        QrPaymentSession $session,
        User $user,
        string $gateway,
        bool $saveCard = false,
        ?string $cardNickname = null,
        string $redirectUrl = ''
    ): array {
        $gatewayService = $this->getGateway($gateway);

        // Create payment transaction record
        $transaction = PaymentTransaction::create([
            'reference' => PaymentTransaction::generateReference(),
            'qr_session_id' => $session->id,
            'customer_id' => $user->id,
            'brand_id' => $session->brand_id,
            'branch_id' => $session->branch_id,
            'original_amount' => $session->original_amount ?? $session->amount,
            'discount_amount' => $session->discount_amount ?? 0,
            'final_amount' => $session->final_amount ?? $session->amount,
            'currency' => $session->currency,
            'bank_offer_id' => $session->bank_offer_id,
            'bank_id' => $session->bankOffer?->bank_id,
            'discount_type' => $session->bankOffer?->offer_type,
            'discount_value' => $session->bankOffer?->offer_value,
            'payment_method' => 'card',
            'gateway' => $gateway,
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone_number,
            'customer_ip' => request()->ip(),
            'metadata' => [
                'qr_session_code' => $session->session_code,
                'save_card' => $saveCard,
                'card_nickname' => $cardNickname,
            ],
        ]);

        $transaction->calculateFees();
        $transaction->save();

        // Initiate payment with gateway
        $request = new PaymentRequestDTO(
            amount: $transaction->final_amount,
            currency: $transaction->currency,
            reference: $transaction->reference,
            customerId: $user->id,
            customerName: $user->name,
            customerEmail: $user->email,
            customerPhone: $user->phone_number,
            redirectUrl: $redirectUrl,
            webhookUrl: route('webhooks.payment.' . $gateway),
            branchId: $session->branch_id,
            brandId: $session->brand_id,
            description: "Payment at " . $session->brand?->name,
            saveCard: $saveCard,
            cardNickname: $cardNickname,
        );

        $response = $gatewayService->initiatePayment($request);

        // Update transaction with gateway response
        $transaction->update([
            'gateway_transaction_id' => $response->transactionId,
            'status' => $response->status === 'pending' ? 'processing' : $response->status,
            'requires_3ds' => $response->requiresRedirect(),
            'auth_url' => $response->redirectUrl,
            'gateway_response' => $response->gatewayResponse,
        ]);

        return [
            'payment_id' => $transaction->id,
            'transaction_id' => $response->transactionId,
            'redirect_url' => $response->redirectUrl,
            'requires_redirect' => $response->requiresRedirect(),
            'status' => $response->status,
        ];
    }

    /**
     * Charge tokenized card for QR payment
     */
    public function chargeTokenForQrPayment(
        QrPaymentSession $session,
        TokenizedCard $card,
        User $user
    ): array {
        $gatewayService = $this->getGateway($card->gateway);

        // Create payment transaction record
        $transaction = PaymentTransaction::create([
            'reference' => PaymentTransaction::generateReference(),
            'qr_session_id' => $session->id,
            'customer_id' => $user->id,
            'brand_id' => $session->brand_id,
            'branch_id' => $session->branch_id,
            'original_amount' => $session->original_amount ?? $session->amount,
            'discount_amount' => $session->discount_amount ?? 0,
            'final_amount' => $session->final_amount ?? $session->amount,
            'currency' => $session->currency,
            'bank_offer_id' => $session->bank_offer_id,
            'bank_id' => $card->bank_id,
            'discount_type' => $session->bankOffer?->offer_type,
            'discount_value' => $session->bankOffer?->offer_value,
            'payment_method' => 'card',
            'gateway' => $card->gateway,
            'tokenized_card_id' => $card->id,
            'card_last_four' => $card->card_last_four,
            'card_brand' => $card->card_brand,
            'card_bin' => $card->bin_prefix,
            'status' => 'processing',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone_number,
            'customer_ip' => request()->ip(),
        ]);

        $transaction->calculateFees();
        $transaction->save();

        // Charge the token
        $request = new PaymentRequestDTO(
            amount: $transaction->final_amount,
            currency: $transaction->currency,
            reference: $transaction->reference,
            customerId: $user->id,
            customerName: $user->name,
            customerEmail: $user->email,
            description: "Payment at " . $session->brand?->name,
        );

        $response = $gatewayService->chargeToken(
            $card->gateway_token,
            $card->gateway_customer_id,
            $request
        );

        // Update transaction
        if ($response->isSuccessful()) {
            $transaction->markAsCompleted(
                $response->transactionId,
                $response->chargeId
            );

            // Record card usage
            $card->recordUsage();

            return [
                'success' => true,
                'payment_id' => $transaction->id,
                'transaction_id' => $response->transactionId,
                'status' => 'completed',
            ];
        }

        $transaction->markAsFailed(
            $response->errorCode ?? 'CHARGE_FAILED',
            $response->errorMessage ?? 'Card charge failed'
        );

        return [
            'success' => false,
            'payment_id' => $transaction->id,
            'error_message' => $response->errorMessage,
            'error_code' => $response->errorCode,
        ];
    }

    /**
     * Process wallet payment (Apple Pay / Google Pay) with Auth + Capture
     */
    public function processWalletPayment(
        QrPaymentSession $session,
        User $user,
        string $walletType,
        string $paymentToken,
        string $gateway = 'tap'
    ): array {
        $gatewayService = $this->getGateway($gateway);

        // Create payment transaction
        $transaction = PaymentTransaction::create([
            'reference' => PaymentTransaction::generateReference(),
            'qr_session_id' => $session->id,
            'customer_id' => $user->id,
            'brand_id' => $session->brand_id,
            'branch_id' => $session->branch_id,
            'original_amount' => $session->amount,
            'discount_amount' => 0, // Will be calculated after authorization
            'final_amount' => $session->amount,
            'currency' => $session->currency,
            'payment_method' => $walletType,
            'gateway' => $gateway,
            'status' => 'processing',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone_number,
            'customer_ip' => request()->ip(),
        ]);

        // Step 1: Authorize full amount
        $request = new PaymentRequestDTO(
            amount: $session->amount,
            currency: $session->currency,
            reference: $transaction->reference,
            customerId: $user->id,
            customerName: $user->name,
            customerEmail: $user->email,
            description: "Payment at " . $session->brand?->name,
        );

        $authResponse = $walletType === 'apple_pay'
            ? $gatewayService->processApplePayToken($paymentToken, $request)
            : $gatewayService->processGooglePayToken($paymentToken, $request);

        if (!$authResponse->success) {
            $transaction->markAsFailed(
                $authResponse->errorCode ?? 'AUTH_FAILED',
                $authResponse->errorMessage ?? 'Authorization failed'
            );

            return [
                'success' => false,
                'error_message' => $authResponse->errorMessage,
            ];
        }

        // Step 2: Extract card BIN and calculate discount
        $cardBin = $authResponse->card['bin'] ?? null;
        $discount = $this->discountService->calculateDiscount(
            $session->amount,
            $cardBin,
            $session->branch_id,
            $user->id
        );

        // Step 3: If authorization was only (status = authorized), capture with discount
        if ($authResponse->status === 'authorized' && $discount['has_discount']) {
            $captureResponse = $gatewayService->capturePayment(
                $authResponse->transactionId,
                $discount['final_amount']
            );

            if (!$captureResponse->success) {
                // Void the authorization
                $gatewayService->voidPayment($authResponse->transactionId);

                $transaction->markAsFailed(
                    'CAPTURE_FAILED',
                    'Payment capture failed'
                );

                return [
                    'success' => false,
                    'error_message' => 'Payment capture failed',
                ];
            }
        }

        // Update transaction with discount info
        $transaction->update([
            'gateway_transaction_id' => $authResponse->transactionId,
            'gateway_charge_id' => $authResponse->chargeId,
            'original_amount' => $session->amount,
            'discount_amount' => $discount['discount_amount'],
            'final_amount' => $discount['final_amount'],
            'bank_offer_id' => $discount['bank_offer_id'],
            'bank_id' => $discount['bank']['id'] ?? null,
            'discount_type' => $discount['offer']['type'] ?? null,
            'discount_value' => $discount['offer']['value'] ?? null,
            'card_last_four' => $authResponse->card['last_four'] ?? null,
            'card_brand' => $authResponse->card['brand'] ?? null,
            'card_bin' => $cardBin,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $transaction->calculateFees();
        $transaction->save();

        // Record redemption if discount applied
        if ($discount['has_discount'] && $discount['bank_offer_id']) {
            $this->discountService->recordRedemption(
                $discount['bank_offer_id'],
                $user->id,
                $session->branch_id,
                $session->amount,
                $discount['discount_amount']
            );
        }

        return [
            'success' => true,
            'payment_id' => $transaction->id,
            'transaction_id' => $authResponse->transactionId,
            'original_amount' => $session->amount,
            'discount_amount' => $discount['discount_amount'],
            'final_amount' => $discount['final_amount'],
            'bank' => $discount['bank'],
            'offer' => $discount['offer'],
            'card' => $authResponse->card,
            'message' => $discount['message'],
        ];
    }

    /**
     * Process webhook callback
     */
    public function processWebhook(string $gateway, array $payload): bool
    {
        $gatewayService = $this->getGateway($gateway);
        $parsed = $gatewayService->parseWebhookPayload($payload);

        // Find transaction by reference or gateway transaction ID
        $transaction = PaymentTransaction::where('gateway_transaction_id', $parsed['transaction_id'])
            ->orWhere('reference', $parsed['reference'])
            ->first();

        if (!$transaction) {
            return false;
        }

        // Update transaction status
        $status = $parsed['status'];

        if ($status === 'completed') {
            $transaction->markAsCompleted(
                $parsed['transaction_id'],
                $payload['id'] ?? null
            );

            // Record bank offer redemption if applicable
            if ($transaction->bank_offer_id) {
                $this->discountService->recordRedemption(
                    $transaction->bank_offer_id,
                    $transaction->customer_id,
                    $transaction->branch_id,
                    $transaction->original_amount,
                    $transaction->discount_amount
                );
            }

            // Update QR session
            if ($transaction->qr_session_id) {
                $session = QrPaymentSession::find($transaction->qr_session_id);
                $session?->markAsCompleted($transaction->id, $parsed['transaction_id']);
            }
        } elseif ($status === 'failed') {
            $transaction->markAsFailed(
                $payload['response']['code'] ?? 'UNKNOWN',
                $payload['response']['message'] ?? 'Payment failed'
            );

            if ($transaction->qr_session_id) {
                $session = QrPaymentSession::find($transaction->qr_session_id);
                $session?->markAsFailed('Payment failed');
            }
        }

        // Save card if requested
        if ($status === 'completed' && ($transaction->metadata['save_card'] ?? false)) {
            $this->saveCardFromTransaction($transaction, $parsed['card'] ?? []);
        }

        return true;
    }

    /**
     * Save card from successful transaction
     */
    private function saveCardFromTransaction(PaymentTransaction $transaction, array $cardData): ?TokenizedCard
    {
        if (empty($cardData['last_four'])) {
            return null;
        }

        return TokenizedCard::create([
            'user_id' => $transaction->customer_id,
            'gateway' => $transaction->gateway,
            'gateway_token' => $cardData['token'] ?? $transaction->gateway_transaction_id,
            'gateway_customer_id' => $cardData['customer_id'] ?? null,
            'card_last_four' => $cardData['last_four'],
            'card_brand' => $cardData['brand'] ?? 'unknown',
            'card_expiry_month' => $cardData['exp_month'] ?? '12',
            'card_expiry_year' => $cardData['exp_year'] ?? '2030',
            'bin_prefix' => $cardData['bin'] ?? $transaction->card_bin,
            'bank_id' => $transaction->bank_id,
            'nickname' => $transaction->metadata['card_nickname'] ?? null,
            'is_active' => true,
            'is_default' => !TokenizedCard::where('user_id', $transaction->customer_id)->exists(),
        ]);
    }

    /**
     * Tokenize and save a new card
     */
    public function saveCard(
        User $user,
        array $cardData,
        string $gateway = 'tap',
        ?string $nickname = null
    ): TokenizedCard {
        $gatewayService = $this->getGateway($gateway);

        $tokenResult = $gatewayService->tokenizeCard($cardData, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone_number,
        ]);

        // Detect bank from BIN
        $cardType = null;
        if (!empty($tokenResult['card']['bin'])) {
            $cardType = \Modules\BankOffer\app\Models\CardType::findByBin($tokenResult['card']['bin']);
        }

        return TokenizedCard::create([
            'user_id' => $user->id,
            'gateway' => $gateway,
            'gateway_token' => $tokenResult['token'],
            'gateway_customer_id' => $tokenResult['customer_id'],
            'card_last_four' => $tokenResult['card']['last_four'],
            'card_brand' => $tokenResult['card']['brand'],
            'card_expiry_month' => $tokenResult['card']['exp_month'],
            'card_expiry_year' => $tokenResult['card']['exp_year'],
            'cardholder_name' => $cardData['cardholder_name'] ?? null,
            'nickname' => $nickname,
            'bin_prefix' => $tokenResult['card']['bin'],
            'bank_id' => $cardType?->bank_id,
            'card_type_id' => $cardType?->id,
            'is_active' => true,
            'is_default' => !TokenizedCard::where('user_id', $user->id)->exists(),
            'is_verified' => true,
        ]);
    }

    /**
     * Delete saved card
     */
    public function deleteCard(TokenizedCard $card): bool
    {
        $gatewayService = $this->getGateway($card->gateway);

        // Delete from gateway
        if ($card->gateway_customer_id) {
            $gatewayService->deleteToken($card->gateway_token, $card->gateway_customer_id);
        }

        // If this was default, make another card default
        if ($card->is_default) {
            $nextCard = TokenizedCard::where('user_id', $card->user_id)
                ->where('id', '!=', $card->id)
                ->active()
                ->first();

            $nextCard?->setAsDefault();
        }

        return $card->delete();
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments(User $user, array $filters = [], int $perPage = 10)
    {
        $query = PaymentTransaction::forCustomer($user->id)
            ->with(['brand', 'branch', 'bankOffer.bank'])
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get payment details
     */
    public function getPayment(int $id, User $user): ?PaymentTransaction
    {
        return PaymentTransaction::where('id', $id)
            ->where('customer_id', $user->id)
            ->with(['brand', 'branch', 'bankOffer.bank', 'qrSession'])
            ->first();
    }

    /**
     * Get enabled payment methods
     */
    public function getEnabledPaymentMethods(): array
    {
        $methods = PaymentMethodSetting::getEnabledMethods();

        if ($methods->isEmpty()) {
            // Return defaults from config
            return collect(config('payment.methods', []))
                ->filter(fn($m) => $m['enabled'] ?? false)
                ->map(fn($m, $key) => [
                    'method' => $key,
                    'name' => $m['name'],
                    'name_ar' => $m['name_ar'],
                    'icon' => $m['icon'],
                ])
                ->values()
                ->toArray();
        }

        return $methods->map(fn($m) => [
            'method' => $m->method,
            'name' => $m->display_name,
            'name_ar' => $m->display_name_ar,
            'icon' => $m->icon,
        ])->toArray();
    }
}
