<?php

namespace Modules\Payment\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\app\Models\PaymentMethodSetting;
use Modules\Payment\Services\BankDiscountService;

class RetailerQrSessionController extends Controller
{
    public function __construct(
        private readonly BankDiscountService $discountService
    ) {}

    /**
     * POST /api/v1/retailer/qr-sessions
     * Create a new QR payment session
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:99999.99',
            'description' => 'nullable|string|max:255',
            'currency' => 'nullable|string|size:3',
        ]);

        // Get authenticated user and their retailer info
        $user = auth()->user();

        // Check if user has retailer access
        $retailer = $user->retailer ?? null;
        if (!$retailer) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'User is not associated with a retailer account',
            ], 403);
        }

        // Check subscription status
        if (!$this->hasActiveSubscription($retailer)) {
            return response()->json([
                'success' => false,
                'error' => 'subscription_required',
                'message' => 'An active subscription is required to create payment sessions',
                'subscription_url' => route('retailer.subscription.plans'),
            ], 402);
        }

        // Get active branch
        $branch = $user->activeBranch ?? $retailer->branches()->first();
        if (!$branch) {
            return response()->json([
                'success' => false,
                'error' => 'no_branch',
                'message' => 'No active branch found for this retailer',
            ], 400);
        }

        // Check if QR payments are enabled
        if (!$this->isQrPaymentEnabled()) {
            return response()->json([
                'success' => false,
                'error' => 'payments_disabled',
                'message' => 'QR payments are currently disabled',
            ], 503);
        }

        try {
            DB::beginTransaction();

            // Create QR session
            $session = QrPaymentSession::create([
                'session_code' => QrPaymentSession::generateSessionCode(),
                'retailer_id' => $retailer->id,
                'branch_id' => $branch->id,
                'brand_id' => $branch->brand_id,
                'created_by_user_id' => $user->id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'JOD',
                'description' => $request->description,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(config('payment.qr.expiry_minutes', 15)),
            ]);

            // Generate QR code
            $session->generateQrCode();

            DB::commit();

            // Get available offers for display
            $availableOffers = $this->discountService->getAvailableOffers($branch->id, $request->amount);

            return response()->json([
                'success' => true,
                'data' => [
                    'session_code' => $session->session_code,
                    'qr_code_image' => $session->qr_code_image,
                    'qr_code_data' => $session->qr_code_data,
                    'amount' => (float) $session->amount,
                    'currency' => $session->currency,
                    'description' => $session->description,
                    'retailer' => [
                        'id' => $retailer->id,
                        'name' => $branch->brand->name ?? $retailer->business_name,
                        'name_ar' => $branch->brand->name_ar ?? null,
                        'logo' => $branch->brand->logo_url ?? null,
                    ],
                    'branch' => [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'name_ar' => $branch->name_ar ?? null,
                        'location' => $branch->location ?? null,
                    ],
                    'status' => $session->status,
                    'expires_at' => $session->expires_at->toIso8601String(),
                    'expires_in_seconds' => now()->diffInSeconds($session->expires_at),
                    'created_at' => $session->created_at->toIso8601String(),
                    'available_offers' => $availableOffers,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'creation_failed',
                'message' => 'Failed to create payment session',
            ], 500);
        }
    }

    /**
     * GET /api/v1/retailer/qr-sessions/{code}
     * Get session details
     */
    public function show(string $code): JsonResponse
    {
        $retailer = auth()->user()->retailer;

        if (!$retailer) {
            return response()->json([
                'success' => false,
                'message' => 'Retailer not found',
            ], 403);
        }

        $session = QrPaymentSession::where('session_code', $code)
            ->where('retailer_id', $retailer->id)
            ->with(['branch', 'brand', 'customer', 'payment', 'bankOffer.bank'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatSessionDetails($session),
        ]);
    }

    /**
     * GET /api/v1/retailer/qr-sessions/{code}/status
     * Get session status (for polling)
     */
    public function status(string $code): JsonResponse
    {
        $retailer = auth()->user()->retailer;

        if (!$retailer) {
            return response()->json([
                'success' => false,
                'message' => 'Retailer not found',
            ], 403);
        }

        $session = QrPaymentSession::where('session_code', $code)
            ->where('retailer_id', $retailer->id)
            ->with(['customer', 'payment', 'bankOffer.bank'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        // Check if session expired
        if ($session->status === 'pending' && $session->isExpired()) {
            $session->update(['status' => 'expired']);
        }

        $data = [
            'session_code' => $session->session_code,
            'status' => $session->status,
            'amount' => (float) $session->amount,
            'currency' => $session->currency,
            'expires_at' => $session->expires_at->toIso8601String(),
            'expires_in_seconds' => max(0, now()->diffInSeconds($session->expires_at, false)),
            'is_expired' => $session->isExpired(),
        ];

        // Add scanned info
        if (in_array($session->status, ['scanned', 'processing', 'completed'])) {
            $data['scanned_at'] = $session->scanned_at?->toIso8601String();
            $data['customer'] = $session->customer ? [
                'name' => $this->maskName($session->customer->name),
                'phone_last_four' => $session->customer->phone ? substr($session->customer->phone, -4) : null,
            ] : null;
        }

        // Add processing info
        if (in_array($session->status, ['processing', 'completed'])) {
            $data['original_amount'] = (float) $session->original_amount;
            $data['discount_amount'] = (float) $session->discount_amount;
            $data['final_amount'] = (float) $session->final_amount;

            if ($session->bankOffer) {
                $data['bank_offer'] = [
                    'bank_name' => $session->bankOffer->bank->name ?? null,
                    'bank_logo' => $session->bankOffer->bank->logo?->url ?? null,
                    'offer_display' => $session->bankOffer->discount_display,
                ];
            }
        }

        // Add completed info
        if ($session->status === 'completed') {
            $data['payment'] = [
                'id' => $session->payment_id,
                'transaction_id' => $session->gateway_transaction_id,
                'gateway' => $session->gateway,
                'payment_method' => $session->payment_method,
            ];

            if ($session->payment) {
                $data['payment']['card_last_four'] = $session->payment->card_last_four;
                $data['payment']['card_brand'] = $session->payment->card_brand;
            }

            $data['completed_at'] = $session->completed_at?->toIso8601String();
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * POST /api/v1/retailer/qr-sessions/{code}/cancel
     * Cancel a pending session
     */
    public function cancel(string $code): JsonResponse
    {
        $retailer = auth()->user()->retailer;

        if (!$retailer) {
            return response()->json([
                'success' => false,
                'message' => 'Retailer not found',
            ], 403);
        }

        $session = QrPaymentSession::where('session_code', $code)
            ->where('retailer_id', $retailer->id)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        if (!in_array($session->status, ['pending', 'scanned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Session cannot be cancelled in current status',
            ], 400);
        }

        $session->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => auth()->id(),
        ]);

        // Broadcast cancellation event (will be implemented later)
        // broadcast(new QrSessionCancelled($session))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Session cancelled successfully',
            'data' => [
                'session_code' => $session->session_code,
                'status' => $session->status,
            ],
        ]);
    }

    /**
     * GET /api/v1/retailer/qr-sessions
     * List sessions (history)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,scanned,processing,completed,expired,cancelled',
            'branch_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $retailer = auth()->user()->retailer;

        if (!$retailer) {
            return response()->json([
                'success' => false,
                'message' => 'Retailer not found',
            ], 403);
        }

        $query = QrPaymentSession::where('retailer_id', $retailer->id)
            ->with(['branch', 'brand', 'customer', 'bankOffer.bank']);

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sessions = $query->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $sessions->map(fn($session) => $this->formatSessionSummary($session)),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/retailer/qr-sessions/stats
     * Get session statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $retailer = auth()->user()->retailer;

        if (!$retailer) {
            return response()->json([
                'success' => false,
                'message' => 'Retailer not found',
            ], 403);
        }

        $query = QrPaymentSession::where('retailer_id', $retailer->id);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_sessions' => (clone $query)->count(),
            'completed_sessions' => (clone $query)->where('status', 'completed')->count(),
            'pending_sessions' => (clone $query)->where('status', 'pending')->count(),
            'expired_sessions' => (clone $query)->where('status', 'expired')->count(),
            'cancelled_sessions' => (clone $query)->where('status', 'cancelled')->count(),
            'total_amount' => (clone $query)->where('status', 'completed')->sum('final_amount'),
            'total_discount' => (clone $query)->where('status', 'completed')->sum('discount_amount'),
            'conversion_rate' => 0,
        ];

        if ($stats['total_sessions'] > 0) {
            $stats['conversion_rate'] = round(
                ($stats['completed_sessions'] / $stats['total_sessions']) * 100,
                2
            );
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Check if retailer has active subscription
     */
    private function hasActiveSubscription($retailer): bool
    {
        // Check if subscription module exists and retailer has active subscription
        if (method_exists($retailer, 'hasActiveSubscription')) {
            return $retailer->hasActiveSubscription();
        }

        // Check subscription relationship
        if (method_exists($retailer, 'subscription')) {
            $subscription = $retailer->subscription;
            return $subscription && $subscription->is_active && $subscription->expires_at > now();
        }

        // Fallback: Check subscriptions table directly
        return DB::table('subscriptions')
            ->where('retailer_id', $retailer->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Check if QR payments are enabled
     */
    private function isQrPaymentEnabled(): bool
    {
        // Check payment method settings
        $cardEnabled = PaymentMethodSetting::isMethodEnabled('card');
        $cliqEnabled = PaymentMethodSetting::isMethodEnabled('cliq');
        $applePayEnabled = PaymentMethodSetting::isMethodEnabled('apple_pay');
        $googlePayEnabled = PaymentMethodSetting::isMethodEnabled('google_pay');

        // At least one payment method must be enabled
        return $cardEnabled || $cliqEnabled || $applePayEnabled || $googlePayEnabled;
    }

    /**
     * Mask customer name for privacy
     */
    private function maskName(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        $parts = explode(' ', $name);
        $masked = [];

        foreach ($parts as $part) {
            if (strlen($part) > 2) {
                $masked[] = substr($part, 0, 1) . str_repeat('*', strlen($part) - 1);
            } else {
                $masked[] = $part;
            }
        }

        return implode(' ', $masked);
    }

    /**
     * Format session details for response
     */
    private function formatSessionDetails(QrPaymentSession $session): array
    {
        $data = [
            'session_code' => $session->session_code,
            'qr_code_image' => $session->qr_code_image,
            'qr_code_data' => $session->qr_code_data,
            'amount' => (float) $session->amount,
            'currency' => $session->currency,
            'description' => $session->description,
            'status' => $session->status,
            'retailer' => [
                'id' => $session->retailer_id,
                'name' => $session->brand?->name,
                'name_ar' => $session->brand?->name_ar,
            ],
            'branch' => [
                'id' => $session->branch_id,
                'name' => $session->branch?->name,
                'name_ar' => $session->branch?->name_ar,
                'location' => $session->branch?->location,
            ],
            'created_by' => [
                'id' => $session->created_by_user_id,
                'name' => $session->createdBy?->name,
            ],
            'created_at' => $session->created_at->toIso8601String(),
            'expires_at' => $session->expires_at->toIso8601String(),
        ];

        if ($session->customer_id) {
            $data['customer'] = [
                'id' => $session->customer_id,
                'name' => $session->customer?->name,
                'phone' => $session->customer?->phone,
                'email' => $session->customer?->email,
            ];
            $data['scanned_at'] = $session->scanned_at?->toIso8601String();
        }

        if ($session->original_amount) {
            $data['original_amount'] = (float) $session->original_amount;
            $data['discount_amount'] = (float) $session->discount_amount;
            $data['final_amount'] = (float) $session->final_amount;
        }

        if ($session->bankOffer) {
            $data['bank_offer'] = [
                'id' => $session->bank_offer_id,
                'bank_name' => $session->bankOffer->bank?->name,
                'bank_name_ar' => $session->bankOffer->bank?->name_ar,
                'bank_logo' => $session->bankOffer->bank?->logo?->url,
                'offer_title' => $session->bankOffer->title,
                'offer_display' => $session->bankOffer->discount_display,
            ];
        }

        if ($session->payment_id) {
            $data['payment'] = [
                'id' => $session->payment_id,
                'transaction_id' => $session->gateway_transaction_id,
                'gateway' => $session->gateway,
                'payment_method' => $session->payment_method,
                'card_last_four' => $session->payment?->card_last_four,
                'card_brand' => $session->payment?->card_brand,
            ];
            $data['completed_at'] = $session->completed_at?->toIso8601String();
        }

        return $data;
    }

    /**
     * Format session summary for list
     */
    private function formatSessionSummary(QrPaymentSession $session): array
    {
        return [
            'session_code' => $session->session_code,
            'amount' => (float) $session->amount,
            'final_amount' => $session->final_amount ? (float) $session->final_amount : null,
            'discount_amount' => $session->discount_amount ? (float) $session->discount_amount : null,
            'currency' => $session->currency,
            'status' => $session->status,
            'branch' => [
                'id' => $session->branch_id,
                'name' => $session->branch?->name,
            ],
            'customer' => $session->customer ? [
                'name' => $this->maskName($session->customer->name),
            ] : null,
            'bank_offer' => $session->bankOffer ? [
                'bank_name' => $session->bankOffer->bank?->name,
                'offer_display' => $session->bankOffer->discount_display,
            ] : null,
            'created_at' => $session->created_at->toIso8601String(),
            'completed_at' => $session->completed_at?->toIso8601String(),
        ];
    }
}
