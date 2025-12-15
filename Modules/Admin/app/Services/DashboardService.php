<?php

namespace Modules\Admin\app\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Admin\app\Repositories\Contracts\OnboardingRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\Contracts\DashboardServiceInterface;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OnboardingRepositoryInterface $onboardingRepository,
        protected PlanRepositoryInterface $planRepository
    ) {}

    public function getStats(): array
    {
        $cacheTtl = config('admin.cache.stats_ttl', 300);

        return Cache::remember('admin.dashboard.stats', $cacheTtl, function () {
            return [
                'users' => $this->getUserStats(),
                'onboardings' => $this->getOnboardingStats(),
                'subscriptions' => $this->getSubscriptionStats(),
                'payments' => $this->getPaymentStats(),
                'plans' => $this->getPlanStats(),
            ];
        });
    }

    public function getUserStats(): array
    {
        return [
            'total' => $this->userRepository->all()->count(),
            'this_month' => $this->userRepository->countThisMonth(),
        ];
    }

    public function getOnboardingStats(): array
    {
        return [
            'total' => $this->onboardingRepository->countTotal(),
            'in_progress' => $this->onboardingRepository->countInProgress(),
            'completed' => $this->onboardingRepository->countCompleted(),
        ];
    }

    public function getSubscriptionStats(): array
    {
        return [
            'total' => RetailerSubscription::count(),
            'active' => RetailerSubscription::where('status', 'active')->count(),
            'pending' => RetailerSubscription::where('status', 'pending')->count(),
        ];
    }

    public function getPaymentStats(): array
    {
        return [
            'total_revenue' => SubscriptionPayment::where('status', 'completed')->sum('total'),
            'this_month_revenue' => SubscriptionPayment::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
        ];
    }

    public function getPlanStats(): array
    {
        return [
            'total' => $this->planRepository->countTotal(),
            'active' => $this->planRepository->countActive(),
        ];
    }

    public function clearStatsCache(): void
    {
        Cache::forget('admin.dashboard.stats');
    }
}
