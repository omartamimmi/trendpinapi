<?php

namespace Modules\Admin\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

class OnboardingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $admin): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function view(User $admin, RetailerOnboarding $onboarding): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function approve(User $admin, RetailerOnboarding $onboarding): bool
    {
        if ($onboarding->status !== 'pending_approval') {
            return false;
        }

        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function reject(User $admin, RetailerOnboarding $onboarding): bool
    {
        if (!in_array($onboarding->status, ['pending_approval', 'pending'])) {
            return false;
        }

        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function requestChanges(User $admin, RetailerOnboarding $onboarding): bool
    {
        if ($onboarding->status !== 'pending_approval') {
            return false;
        }

        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }
}
