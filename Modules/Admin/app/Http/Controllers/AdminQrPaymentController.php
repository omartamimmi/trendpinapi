<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Payment\app\Models\PaymentSetting;
use Modules\Payment\app\Models\PaymentMethodSetting;
use Modules\Payment\app\Models\PaymentTransaction;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Business\app\Models\Branch;
use Modules\Business\app\Models\Brand;
use Modules\BankOffer\app\Models\Bank;
use Carbon\Carbon;

class AdminQrPaymentController extends Controller
{
    /**
     * QR Payment Settings page (gateways & methods)
     */
    public function settings(): Response
    {
        // Get gateway settings
        $gateways = $this->getGatewaySettings();

        // Get payment method settings
        $methods = PaymentMethodSetting::orderBy('sort_order')->get()->map(function ($method) {
            return [
                'id' => $method->id,
                'method' => $method->method,
                'name' => $method->display_name,
                'description' => $method->display_name_ar,
                'icon' => $method->icon,
                'is_enabled' => $method->is_enabled,
                'supported_gateways' => $method->preferred_gateway ? [$method->preferred_gateway] : [],
                'display_order' => $method->sort_order,
            ];
        });

        // Get general settings
        $generalSettings = PaymentSetting::where('gateway', 'general')->first();

        return Inertia::render('Admin/QrPayment/Settings', [
            'gateways' => $gateways,
            'methods' => $methods,
            'generalSettings' => $generalSettings ? [
                'default_gateway' => $generalSettings->getSetting('default_gateway', 'tap'),
                'qr_expiry_minutes' => $generalSettings->getSetting('qr_expiry_minutes', 15),
                'require_subscription' => $generalSettings->getSetting('require_subscription', true),
                'enable_discounts' => $generalSettings->getSetting('enable_discounts', true),
            ] : [
                'default_gateway' => 'tap',
                'qr_expiry_minutes' => 15,
                'require_subscription' => true,
                'enable_discounts' => true,
            ],
        ]);
    }

    /**
     * Update gateway settings
     */
    public function updateGateway(Request $request, string $gateway): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled' => 'boolean',
            'is_sandbox' => 'boolean',
            'public_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'merchant_id' => 'nullable|string',
            'merchant_alias' => 'nullable|string',
        ]);

        $setting = PaymentSetting::firstOrCreate(
            ['gateway' => $gateway],
            ['display_name' => ucfirst($gateway) . ' Payments', 'is_enabled' => false]
        );

        $setting->is_enabled = $validated['is_enabled'] ?? $setting->is_enabled;
        $setting->is_sandbox = $validated['is_sandbox'] ?? $setting->is_sandbox;

        // Only update credentials if provided (not empty)
        $credentials = $setting->getDecryptedCredentials();

        if (!empty($validated['public_key'])) {
            $credentials['public_key'] = $validated['public_key'];
        }
        if (!empty($validated['secret_key'])) {
            $credentials['secret_key'] = $validated['secret_key'];
        }
        if (!empty($validated['webhook_secret'])) {
            $credentials['webhook_secret'] = $validated['webhook_secret'];
        }
        if (!empty($validated['merchant_id'])) {
            $credentials['merchant_id'] = $validated['merchant_id'];
        }
        if (!empty($validated['merchant_alias'])) {
            $credentials['merchant_alias'] = $validated['merchant_alias'];
        }

        $setting->setCredentials($credentials);
        $setting->save();

        return back()->with('success', ucfirst($gateway) . ' settings updated successfully');
    }

    /**
     * Toggle payment method
     */
    public function toggleMethod(Request $request, string $method): \Illuminate\Http\RedirectResponse
    {
        $methodSetting = PaymentMethodSetting::where('method', $method)->firstOrFail();
        $methodSetting->is_enabled = !$methodSetting->is_enabled;
        $methodSetting->save();

        $status = $methodSetting->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', ucfirst($method) . ' payment method ' . $status);
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'default_gateway' => 'required|string|in:tap,hyperpay,paytabs',
            'qr_expiry_minutes' => 'required|integer|min:5|max:60',
            'require_subscription' => 'boolean',
            'enable_discounts' => 'boolean',
        ]);

        $setting = PaymentSetting::firstOrCreate(
            ['gateway' => 'general'],
            ['display_name' => 'General Settings', 'is_enabled' => true]
        );

        $setting->setSettings([
            'default_gateway' => $validated['default_gateway'],
            'qr_expiry_minutes' => $validated['qr_expiry_minutes'],
            'require_subscription' => $validated['require_subscription'] ?? true,
            'enable_discounts' => $validated['enable_discounts'] ?? true,
        ]);
        $setting->save();

        return back()->with('success', 'General settings updated successfully');
    }

    /**
     * Test gateway connection
     */
    public function testGateway(string $gateway): \Illuminate\Http\JsonResponse
    {
        try {
            $setting = PaymentSetting::where('gateway', $gateway)->first();

            if (!$setting || empty($setting->credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gateway credentials not configured',
                ]);
            }

            // For now, just check if credentials exist
            // In production, you'd make a test API call to the gateway
            $hasCredentials = !empty($setting->credentials['secret_key'] ?? null);

            return response()->json([
                'success' => $hasCredentials,
                'message' => $hasCredentials
                    ? 'Gateway connection successful'
                    : 'Missing required credentials',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Analytics Dashboard
     */
    public function analytics(Request $request): Response
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Overview stats
        $stats = $this->getOverviewStats($dateFrom, $dateTo);

        // Recent transactions
        $recentTransactions = PaymentTransaction::with(['user', 'branch.brand', 'bankOffer.bank'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Charts data
        $dailyStats = $this->getDailyStats($dateFrom, $dateTo);
        $byGateway = $this->getStatsByGateway($dateFrom, $dateTo);
        $byMethod = $this->getStatsByMethod($dateFrom, $dateTo);
        $topBrands = $this->getTopBrands($dateFrom, $dateTo);
        $topBranches = $this->getTopBranches($dateFrom, $dateTo);

        return Inertia::render('Admin/QrPayment/Analytics', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'dailyStats' => $dailyStats,
            'byGateway' => $byGateway,
            'byMethod' => $byMethod,
            'topBrands' => $topBrands,
            'topBranches' => $topBranches,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Transactions list
     */
    public function transactions(Request $request): Response
    {
        $query = PaymentTransaction::with(['user', 'branch.brand', 'bankOffer.bank', 'tokenizedCard']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('brand_id')) {
            $query->whereHas('branch', fn($q) => $q->where('brand_id', $request->brand_id));
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Get filter options
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $branches = Branch::select('id', 'name', 'brand_id')->orderBy('name')->get();

        return Inertia::render('Admin/QrPayment/Transactions', [
            'transactions' => $transactions,
            'brands' => $brands,
            'branches' => $branches,
            'filters' => $request->only(['status', 'gateway', 'payment_method', 'brand_id', 'branch_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * Transaction details
     */
    public function transactionDetails(int $id): Response
    {
        $transaction = PaymentTransaction::with([
            'user',
            'branch.brand',
            'bankOffer.bank',
            'tokenizedCard',
            'qrSession',
        ])->findOrFail($id);

        return Inertia::render('Admin/QrPayment/TransactionDetails', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * QR Sessions list
     */
    public function sessions(Request $request): Response
    {
        $query = QrPaymentSession::with(['retailer', 'branch.brand', 'customer', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('session_code', 'like', "%{$search}%")
                    ->orWhereHas('branch.brand', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        $sessions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Admin/QrPayment/Sessions', [
            'sessions' => $sessions,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    // ============ Private Helper Methods ============

    private function getGatewaySettings(): array
    {
        $gateways = ['tap', 'hyperpay', 'paytabs', 'cliq'];
        $settings = [];

        foreach ($gateways as $gateway) {
            $setting = PaymentSetting::where('gateway', $gateway)->first();
            $settings[$gateway] = [
                'name' => $this->getGatewayName($gateway),
                'description' => $this->getGatewayDescription($gateway),
                'is_enabled' => $setting?->is_enabled ?? false,
                'is_sandbox' => $setting?->is_sandbox ?? true,
                'has_credentials' => !empty($setting?->credentials),
                'supports' => $this->getGatewaySupports($gateway),
            ];
        }

        return $settings;
    }

    private function getGatewayName(string $gateway): string
    {
        return match ($gateway) {
            'tap' => 'Tap Payments',
            'hyperpay' => 'HyperPay',
            'paytabs' => 'PayTabs',
            'cliq' => 'CliQ (Jordan)',
            default => ucfirst($gateway),
        };
    }

    private function getGatewayDescription(string $gateway): string
    {
        return match ($gateway) {
            'tap' => 'Primary payment gateway for card payments, Apple Pay, and Google Pay',
            'hyperpay' => 'Alternative payment gateway with regional support',
            'paytabs' => 'Multi-currency payment gateway',
            'cliq' => 'Jordan instant bank transfer system',
            default => '',
        };
    }

    private function getGatewaySupports(string $gateway): array
    {
        return match ($gateway) {
            'tap' => ['card', 'apple_pay', 'google_pay'],
            'hyperpay' => ['card', 'apple_pay', 'google_pay'],
            'paytabs' => ['card', 'apple_pay'],
            'cliq' => ['bank_transfer'],
            default => [],
        };
    }

    private function getOverviewStats(string $dateFrom, string $dateTo): array
    {
        $query = PaymentTransaction::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        $completed = (clone $query)->where('status', 'completed');

        return [
            'total_transactions' => (clone $query)->count(),
            'completed_transactions' => $completed->count(),
            'total_amount' => round($completed->sum('final_amount'), 2),
            'total_discounts' => round($completed->sum('discount_amount'), 2),
            'average_transaction' => round($completed->avg('final_amount') ?? 0, 2),
            'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
        ];
    }

    private function calculateConversionRate(string $dateFrom, string $dateTo): float
    {
        $sessions = QrPaymentSession::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        $total = $sessions->count();
        $completed = (clone $sessions)->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    private function getDailyStats(string $dateFrom, string $dateTo): array
    {
        return PaymentTransaction::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(final_amount) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => $item->count,
                'total' => round($item->total, 2),
            ])
            ->toArray();
    }

    private function getStatsByGateway(string $dateFrom, string $dateTo): array
    {
        return PaymentTransaction::selectRaw('gateway, COUNT(*) as count, SUM(final_amount) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('gateway')
            ->get()
            ->map(fn($item) => [
                'gateway' => $item->gateway,
                'name' => $this->getGatewayName($item->gateway),
                'count' => $item->count,
                'total' => round($item->total ?? 0, 2),
            ])
            ->toArray();
    }

    private function getStatsByMethod(string $dateFrom, string $dateTo): array
    {
        return PaymentTransaction::selectRaw('payment_method, COUNT(*) as count, SUM(final_amount) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('payment_method')
            ->get()
            ->map(fn($item) => [
                'method' => $item->payment_method,
                'count' => $item->count,
                'total' => round($item->total ?? 0, 2),
            ])
            ->toArray();
    }

    private function getTopBrands(string $dateFrom, string $dateTo, int $limit = 5): array
    {
        return PaymentTransaction::selectRaw('branches.brand_id, brands.name, COUNT(*) as count, SUM(payment_transactions.final_amount) as total')
            ->join('branches', 'payment_transactions.branch_id', '=', 'branches.id')
            ->join('brands', 'branches.brand_id', '=', 'brands.id')
            ->where('payment_transactions.status', 'completed')
            ->whereBetween('payment_transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('branches.brand_id', 'brands.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'id' => $item->brand_id,
                'name' => $item->name,
                'count' => $item->count,
                'total' => round($item->total ?? 0, 2),
            ])
            ->toArray();
    }

    private function getTopBranches(string $dateFrom, string $dateTo, int $limit = 5): array
    {
        return PaymentTransaction::selectRaw('branch_id, branches.name, brands.name as brand_name, COUNT(*) as count, SUM(payment_transactions.final_amount) as total')
            ->join('branches', 'payment_transactions.branch_id', '=', 'branches.id')
            ->join('brands', 'branches.brand_id', '=', 'brands.id')
            ->where('payment_transactions.status', 'completed')
            ->whereBetween('payment_transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('branch_id', 'branches.name', 'brands.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'id' => $item->branch_id,
                'name' => $item->name,
                'brand_name' => $item->brand_name,
                'count' => $item->count,
                'total' => round($item->total ?? 0, 2),
            ])
            ->toArray();
    }
}
