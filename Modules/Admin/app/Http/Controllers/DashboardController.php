<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Modules\Shop\Models\Shop;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'this_month' => User::whereMonth('created_at', now()->month)->count(),
                'by_role' => User::withCount('roles')
                    ->get()
                    ->groupBy(fn($user) => $user->roles->first()?->name ?? 'No Role')
                    ->map->count(),
            ],
            'onboardings' => [
                'total' => RetailerOnboarding::count(),
                'in_progress' => RetailerOnboarding::where('status', 'in_progress')->count(),
                'completed' => RetailerOnboarding::where('status', 'completed')->count(),
            ],
            'subscriptions' => [
                'total' => RetailerSubscription::count(),
                'active' => RetailerSubscription::where('status', 'active')->count(),
                'pending' => RetailerSubscription::where('status', 'pending')->count(),
                'expired' => RetailerSubscription::where('status', 'expired')->count(),
            ],
            'payments' => [
                'total' => SubscriptionPayment::count(),
                'completed' => SubscriptionPayment::where('status', 'completed')->count(),
                'pending' => SubscriptionPayment::where('status', 'pending')->count(),
                'total_revenue' => SubscriptionPayment::where('status', 'completed')->sum('total'),
                'this_month_revenue' => SubscriptionPayment::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total'),
            ],
            'plans' => [
                'total' => SubscriptionPlan::count(),
                'active' => SubscriptionPlan::where('is_active', true)->count(),
            ],
        ];

        // Add shops count if module exists
        if (class_exists(Shop::class)) {
            $stats['shops'] = [
                'total' => Shop::count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activities
     */
    public function recentActivities(): JsonResponse
    {
        $activities = [
            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
            'recent_onboardings' => RetailerOnboarding::with('user:id,name,email')
                ->latest()
                ->take(5)
                ->get(),
            'recent_payments' => SubscriptionPayment::with(['user:id,name,email', 'subscription.plan'])
                ->latest()
                ->take(5)
                ->get(),
            'pending_payments' => SubscriptionPayment::with(['user:id,name,email', 'subscription.plan'])
                ->where('status', 'pending')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
}
