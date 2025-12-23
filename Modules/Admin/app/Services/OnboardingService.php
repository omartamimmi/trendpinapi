<?php

namespace Modules\Admin\app\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\app\Events\OnboardingApproved;
use Modules\Admin\app\Events\OnboardingRejected;
use Modules\Admin\app\Repositories\Contracts\OnboardingRepositoryInterface;
use Modules\Admin\app\Services\Contracts\OnboardingServiceInterface;
use Modules\Business\app\Models\Brand;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;

class OnboardingService implements OnboardingServiceInterface
{
    public function __construct(
        protected OnboardingRepositoryInterface $onboardingRepository
    ) {}

    public function getOnboardings(string $status, ?string $search = null): LengthAwarePaginator
    {
        $perPage = config('admin.pagination.per_page', 20);
        return $this->onboardingRepository->paginateByStatus($status, $search, $perPage);
    }

    public function getOnboarding(int $id): RetailerOnboarding
    {
        return $this->onboardingRepository->findWithRelations($id);
    }

    public function getOnboardingDetails(int $id): array
    {
        $onboarding = RetailerOnboarding::with(['user', 'approver'])->findOrFail($id);
        $user = $onboarding->user;
        $brands = Brand::where('create_user', $user->id)->with(['branches'])->get();
        $subscriptions = RetailerSubscription::where('user_id', $user->id)->with('plan')->get();

        return [
            'onboarding' => $onboarding,
            'retailer' => $user,
            'brands' => $brands,
            'subscriptions' => $subscriptions,
        ];
    }

    public function getCounts(): array
    {
        return [
            'pending' => RetailerOnboarding::where('approval_status', 'pending')->count(),
            'pending_approval' => RetailerOnboarding::where('approval_status', 'pending_approval')->count(),
            'approved' => RetailerOnboarding::where('approval_status', 'approved')->count(),
            'changes_requested' => RetailerOnboarding::where('approval_status', 'changes_requested')->count(),
            'rejected' => RetailerOnboarding::where('approval_status', 'rejected')->count(),
        ];
    }

    public function approve(int $id, ?string $notes = null): RetailerOnboarding
    {
        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'approved',
            'admin_notes' => $notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Activate the retailer's subscription
        $this->activateSubscription($onboarding->user_id);

        event(new OnboardingApproved($onboarding, Auth::user()));

        return $onboarding;
    }

    public function requestChanges(int $id, string $notes): RetailerOnboarding
    {
        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'changes_requested',
            'admin_notes' => $notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return $onboarding;
    }

    public function reject(int $id, string $notes): RetailerOnboarding
    {
        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'rejected',
            'admin_notes' => $notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        event(new OnboardingRejected($onboarding, Auth::user(), $notes));

        return $onboarding;
    }

    protected function activateSubscription(int $userId): void
    {
        $subscription = RetailerSubscription::where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }
    }
}
