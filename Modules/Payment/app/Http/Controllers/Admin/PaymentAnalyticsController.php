<?php

namespace Modules\Payment\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Payment\app\Models\PaymentTransaction;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\app\Models\TokenizedCard;
use Modules\BankOffer\app\Models\BankOfferRedemption;

class PaymentAnalyticsController extends Controller
{
    /**
     * GET /api/v1/admin/payment/analytics/dashboard
     * Get dashboard overview statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        // Payment Summary
        $paymentSummary = $this->getPaymentSummary($dateFrom, $dateTo);

        // QR Session Summary
        $sessionSummary = $this->getSessionSummary($dateFrom, $dateTo);

        // Discount Summary
        $discountSummary = $this->getDiscountSummary($dateFrom, $dateTo);

        // Recent Activity
        $recentActivity = $this->getRecentActivity();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $dateFrom->toDateString(),
                    'to' => $dateTo->toDateString(),
                ],
                'payments' => $paymentSummary,
                'sessions' => $sessionSummary,
                'discounts' => $discountSummary,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/transactions
     * Get detailed transaction analytics
     */
    public function transactions(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'gateway' => 'nullable|string|in:tap,hyperpay,paytabs,cliq',
            'payment_method' => 'nullable|string|in:card,apple_pay,google_pay,cliq',
            'status' => 'nullable|string|in:completed,failed,refunded,pending',
            'brand_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $query = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->gateway) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $transactions = $query->with(['brand', 'branch', 'customer', 'bankOffer.bank'])
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $transactions->map(fn($tx) => [
                'id' => $tx->id,
                'reference' => $tx->reference,
                'transaction_id' => $tx->gateway_transaction_id,
                'amount' => (float) $tx->amount,
                'original_amount' => (float) $tx->original_amount,
                'discount_amount' => (float) $tx->discount_amount,
                'fee_amount' => (float) $tx->fee_amount,
                'net_amount' => (float) $tx->net_amount,
                'currency' => $tx->currency,
                'status' => $tx->status,
                'gateway' => $tx->gateway,
                'payment_method' => $tx->payment_method,
                'brand' => $tx->brand ? [
                    'id' => $tx->brand->id,
                    'name' => $tx->brand->name,
                ] : ['name' => $tx->retailer_name],
                'branch' => $tx->branch ? [
                    'id' => $tx->branch->id,
                    'name' => $tx->branch->name,
                ] : ['name' => $tx->branch_name],
                'customer' => $tx->customer ? [
                    'id' => $tx->customer->id,
                    'name' => $tx->customer->name,
                    'email' => $tx->customer->email,
                    'phone' => $tx->customer->phone,
                ] : [
                    'name' => $tx->customer_name,
                    'email' => $tx->customer_email,
                    'phone' => $tx->customer_phone,
                ],
                'card' => $tx->card_last_four ? [
                    'last_four' => $tx->card_last_four,
                    'brand' => $tx->card_brand,
                ] : null,
                'bank_offer' => $tx->bankOffer ? [
                    'bank_name' => $tx->bankOffer->bank?->name,
                    'offer_display' => $tx->bankOffer->discount_display,
                ] : null,
                'created_at' => $tx->created_at->toIso8601String(),
                'completed_at' => $tx->completed_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/by-gateway
     * Get analytics grouped by gateway
     */
    public function byGateway(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $stats = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                'gateway',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('SUM(fee_amount) as total_fees'),
                DB::raw('AVG(amount) as average_amount'),
            )
            ->groupBy('gateway')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats->map(fn($item) => [
                'gateway' => $item->gateway,
                'transaction_count' => (int) $item->transaction_count,
                'total_amount' => round((float) $item->total_amount, 2),
                'total_discount' => round((float) $item->total_discount, 2),
                'total_fees' => round((float) $item->total_fees, 2),
                'average_amount' => round((float) $item->average_amount, 2),
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/by-method
     * Get analytics grouped by payment method
     */
    public function byMethod(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $stats = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('AVG(amount) as average_amount'),
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats->map(fn($item) => [
                'payment_method' => $item->payment_method,
                'display_name' => $this->getMethodDisplayName($item->payment_method),
                'transaction_count' => (int) $item->transaction_count,
                'total_amount' => round((float) $item->total_amount, 2),
                'total_discount' => round((float) $item->total_discount, 2),
                'average_amount' => round((float) $item->average_amount, 2),
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/by-brand
     * Get analytics grouped by brand/retailer
     */
    public function byBrand(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $stats = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                'brand_id',
                'retailer_name',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers'),
            )
            ->groupBy('brand_id', 'retailer_name')
            ->orderByDesc(DB::raw('SUM(amount)'))
            ->limit(20)
            ->get();

        // Load brand names
        $brandIds = $stats->pluck('brand_id')->filter()->unique();
        $brands = [];
        if ($brandIds->isNotEmpty()) {
            $brands = \Modules\Business\app\Models\Brand::whereIn('id', $brandIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => $stats->map(fn($item) => [
                'brand_id' => $item->brand_id,
                'brand_name' => $brands[$item->brand_id] ?? $item->retailer_name ?? 'Unknown',
                'transaction_count' => (int) $item->transaction_count,
                'total_amount' => round((float) $item->total_amount, 2),
                'total_discount' => round((float) $item->total_discount, 2),
                'unique_customers' => (int) $item->unique_customers,
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/by-branch
     * Get analytics grouped by branch
     */
    public function byBranch(Request $request): JsonResponse
    {
        $request->validate([
            'brand_id' => 'nullable|integer',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $query = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed');

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        $stats = $query->select(
                'branch_id',
                'brand_id',
                'branch_name',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers'),
            )
            ->groupBy('branch_id', 'brand_id', 'branch_name')
            ->orderByDesc(DB::raw('SUM(amount)'))
            ->limit(50)
            ->get();

        // Load branch names
        $branchIds = $stats->pluck('branch_id')->filter()->unique();
        $branches = [];
        if ($branchIds->isNotEmpty()) {
            $branches = \Modules\Business\app\Models\Branch::whereIn('id', $branchIds)
                ->get()
                ->keyBy('id');
        }

        return response()->json([
            'success' => true,
            'data' => $stats->map(fn($item) => [
                'branch_id' => $item->branch_id,
                'branch_name' => $branches[$item->branch_id]?->name ?? $item->branch_name ?? 'Unknown',
                'brand_name' => $branches[$item->branch_id]?->brand?->name ?? null,
                'location' => $branches[$item->branch_id]?->location ?? null,
                'transaction_count' => (int) $item->transaction_count,
                'total_amount' => round((float) $item->total_amount, 2),
                'total_discount' => round((float) $item->total_discount, 2),
                'unique_customers' => (int) $item->unique_customers,
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/by-bank
     * Get analytics grouped by bank (for discount tracking)
     */
    public function byBank(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $stats = BankOfferRedemption::query()
            ->whereBetween('redeemed_at', [$dateFrom, $dateTo])
            ->join('bank_offers', 'bank_offer_redemptions.bank_offer_id', '=', 'bank_offers.id')
            ->join('banks', 'bank_offers.bank_id', '=', 'banks.id')
            ->select(
                'banks.id as bank_id',
                'banks.name as bank_name',
                'banks.name_ar as bank_name_ar',
                DB::raw('COUNT(*) as redemption_count'),
                DB::raw('SUM(bank_offer_redemptions.amount) as total_transaction_amount'),
                DB::raw('SUM(bank_offer_redemptions.discount_applied) as total_discount_given'),
                DB::raw('COUNT(DISTINCT bank_offer_redemptions.user_id) as unique_customers'),
            )
            ->groupBy('banks.id', 'banks.name', 'banks.name_ar')
            ->orderByDesc(DB::raw('SUM(bank_offer_redemptions.discount_applied)'))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats->map(fn($item) => [
                'bank_id' => $item->bank_id,
                'bank_name' => $item->bank_name,
                'bank_name_ar' => $item->bank_name_ar,
                'redemption_count' => (int) $item->redemption_count,
                'total_transaction_amount' => round((float) $item->total_transaction_amount, 2),
                'total_discount_given' => round((float) $item->total_discount_given, 2),
                'unique_customers' => (int) $item->unique_customers,
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/trends
     * Get payment trends over time
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|string|in:daily,weekly,monthly',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $period = $request->period ?? 'daily';
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $groupFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
        };

        $stats = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers'),
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period_type' => $period,
                'trends' => $stats->map(fn($item) => [
                    'period' => $item->period,
                    'transaction_count' => (int) $item->transaction_count,
                    'total_amount' => round((float) $item->total_amount, 2),
                    'total_discount' => round((float) $item->total_discount, 2),
                    'unique_customers' => (int) $item->unique_customers,
                ]),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/conversion
     * Get QR session conversion analytics
     */
    public function conversion(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $sessions = QrPaymentSession::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(*) as total_created'),
                DB::raw('SUM(CASE WHEN status = "scanned" THEN 1 ELSE 0 END) as scanned'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN TIMESTAMPDIFF(SECOND, created_at, completed_at) END) as avg_completion_time'),
            )
            ->first();

        $scanRate = $sessions->total_created > 0
            ? round((($sessions->scanned + $sessions->completed) / $sessions->total_created) * 100, 2)
            : 0;

        $completionRate = ($sessions->scanned + $sessions->completed) > 0
            ? round(($sessions->completed / ($sessions->scanned + $sessions->completed)) * 100, 2)
            : 0;

        $overallConversion = $sessions->total_created > 0
            ? round(($sessions->completed / $sessions->total_created) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_sessions_created' => (int) $sessions->total_created,
                'sessions_scanned' => (int) $sessions->scanned + (int) $sessions->completed,
                'sessions_completed' => (int) $sessions->completed,
                'sessions_expired' => (int) $sessions->expired,
                'sessions_cancelled' => (int) $sessions->cancelled,
                'scan_rate' => $scanRate,
                'completion_rate' => $completionRate,
                'overall_conversion_rate' => $overallConversion,
                'average_completion_time_seconds' => $sessions->avg_completion_time
                    ? round($sessions->avg_completion_time)
                    : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/customers
     * Get customer payment analytics
     */
    public function customers(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        // Top customers by volume
        $topCustomers = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                'customer_name',
                'customer_email',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_spent'),
                DB::raw('SUM(discount_amount) as total_savings'),
            )
            ->groupBy('customer_id', 'customer_name', 'customer_email')
            ->orderByDesc(DB::raw('SUM(amount)'))
            ->limit(20)
            ->get();

        // Customer metrics
        $metrics = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->select(
                DB::raw('COUNT(DISTINCT customer_id) as unique_customers'),
                DB::raw('COUNT(*) / COUNT(DISTINCT customer_id) as avg_transactions_per_customer'),
                DB::raw('SUM(amount) / COUNT(DISTINCT customer_id) as avg_spend_per_customer'),
            )
            ->first();

        // Saved cards stats
        $savedCardsStats = TokenizedCard::query()
            ->select(
                DB::raw('COUNT(*) as total_cards'),
                DB::raw('COUNT(DISTINCT user_id) as users_with_cards'),
                DB::raw('COUNT(*) / COUNT(DISTINCT user_id) as avg_cards_per_user'),
            )
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'top_customers' => $topCustomers->map(fn($c) => [
                    'customer_id' => $c->customer_id,
                    'name' => $c->customer_name,
                    'email' => $c->customer_email,
                    'transaction_count' => (int) $c->transaction_count,
                    'total_spent' => round((float) $c->total_spent, 2),
                    'total_savings' => round((float) $c->total_savings, 2),
                ]),
                'metrics' => [
                    'unique_customers' => (int) $metrics->unique_customers,
                    'avg_transactions_per_customer' => round((float) $metrics->avg_transactions_per_customer, 2),
                    'avg_spend_per_customer' => round((float) $metrics->avg_spend_per_customer, 2),
                ],
                'saved_cards' => [
                    'total_cards' => (int) $savedCardsStats->total_cards,
                    'users_with_cards' => (int) $savedCardsStats->users_with_cards,
                    'avg_cards_per_user' => round((float) $savedCardsStats->avg_cards_per_user, 2),
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/analytics/export
     * Export analytics data
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:transactions,sessions,redemptions',
            'format' => 'nullable|string|in:csv,json',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();
        $format = $request->format ?? 'csv';

        $data = match ($request->type) {
            'transactions' => $this->exportTransactions($dateFrom, $dateTo),
            'sessions' => $this->exportSessions($dateFrom, $dateTo),
            'redemptions' => $this->exportRedemptions($dateFrom, $dateTo),
        };

        // For CSV, generate download URL
        if ($format === 'csv') {
            $filename = "{$request->type}_{$dateFrom->format('Ymd')}_{$dateTo->format('Ymd')}.csv";
            $path = "exports/payment/{$filename}";

            // Store CSV (implementation depends on storage driver)
            // Storage::put($path, $this->arrayToCsv($data));

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => route('admin.payment.export.download', ['filename' => $filename]),
                    'record_count' => count($data),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get payment summary statistics
     */
    private function getPaymentSummary(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        $current = PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total, SUM(discount_amount) as discount')
            ->first();

        // Previous period for comparison
        $periodDays = $dateFrom->diffInDays($dateTo);
        $previousFrom = $dateFrom->copy()->subDays($periodDays);
        $previousTo = $dateFrom->copy()->subDay();

        $previous = PaymentTransaction::query()
            ->whereBetween('created_at', [$previousFrom, $previousTo])
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        $countChange = $previous->count > 0
            ? round((($current->count - $previous->count) / $previous->count) * 100, 2)
            : 0;

        $amountChange = $previous->total > 0
            ? round((($current->total - $previous->total) / $previous->total) * 100, 2)
            : 0;

        return [
            'total_transactions' => (int) $current->count,
            'total_amount' => round((float) $current->total, 2),
            'total_discount' => round((float) $current->discount, 2),
            'average_transaction' => $current->count > 0
                ? round($current->total / $current->count, 2)
                : 0,
            'transaction_count_change' => $countChange,
            'amount_change' => $amountChange,
        ];
    }

    /**
     * Get QR session summary
     */
    private function getSessionSummary(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        $stats = QrPaymentSession::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        return [
            'total_created' => (int) $stats->total,
            'completed' => (int) $stats->completed,
            'expired' => (int) $stats->expired,
            'pending' => (int) $stats->pending,
            'conversion_rate' => $stats->total > 0
                ? round(($stats->completed / $stats->total) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get discount summary
     */
    private function getDiscountSummary(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        $stats = BankOfferRedemption::query()
            ->whereBetween('redeemed_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as count, SUM(discount_applied) as total, AVG(discount_applied) as average')
            ->first();

        return [
            'total_redemptions' => (int) $stats->count,
            'total_discount_given' => round((float) $stats->total, 2),
            'average_discount' => round((float) $stats->average, 2),
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $recentTransactions = PaymentTransaction::query()
            ->with(['brand', 'branch'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($tx) => [
                'type' => 'transaction',
                'id' => $tx->id,
                'amount' => (float) $tx->amount,
                'status' => $tx->status,
                'retailer' => $tx->brand?->name ?? $tx->retailer_name,
                'customer' => $tx->customer_name,
                'created_at' => $tx->created_at->toIso8601String(),
            ]);

        return $recentTransactions->toArray();
    }

    /**
     * Get method display name
     */
    private function getMethodDisplayName(string $method): string
    {
        return match ($method) {
            'card' => 'Credit/Debit Card',
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'cliq' => 'CliQ',
            default => ucfirst($method),
        };
    }

    /**
     * Export transactions data
     */
    private function exportTransactions(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        return PaymentTransaction::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(fn($tx) => [
                'reference' => $tx->reference,
                'transaction_id' => $tx->gateway_transaction_id,
                'amount' => $tx->amount,
                'discount' => $tx->discount_amount,
                'fee' => $tx->fee_amount,
                'status' => $tx->status,
                'gateway' => $tx->gateway,
                'payment_method' => $tx->payment_method,
                'retailer' => $tx->retailer_name,
                'branch' => $tx->branch_name,
                'customer_name' => $tx->customer_name,
                'customer_email' => $tx->customer_email,
                'created_at' => $tx->created_at->toDateTimeString(),
                'completed_at' => $tx->completed_at?->toDateTimeString(),
            ])
            ->toArray();
    }

    /**
     * Export sessions data
     */
    private function exportSessions(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        return QrPaymentSession::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['brand', 'branch'])
            ->get()
            ->map(fn($s) => [
                'session_code' => $s->session_code,
                'amount' => $s->amount,
                'discount' => $s->discount_amount,
                'final_amount' => $s->final_amount,
                'status' => $s->status,
                'retailer' => $s->brand?->name,
                'branch' => $s->branch?->name,
                'created_at' => $s->created_at->toDateTimeString(),
                'scanned_at' => $s->scanned_at?->toDateTimeString(),
                'completed_at' => $s->completed_at?->toDateTimeString(),
            ])
            ->toArray();
    }

    /**
     * Export redemptions data
     */
    private function exportRedemptions(\Carbon\Carbon $dateFrom, \Carbon\Carbon $dateTo): array
    {
        return BankOfferRedemption::query()
            ->whereBetween('redeemed_at', [$dateFrom, $dateTo])
            ->with(['bankOffer.bank', 'brand', 'branch'])
            ->get()
            ->map(fn($r) => [
                'bank' => $r->bankOffer?->bank?->name,
                'offer' => $r->bankOffer?->title,
                'amount' => $r->amount,
                'discount' => $r->discount_applied,
                'retailer' => $r->brand?->name,
                'branch' => $r->branch?->name,
                'redeemed_at' => $r->redeemed_at->toDateTimeString(),
            ])
            ->toArray();
    }
}
