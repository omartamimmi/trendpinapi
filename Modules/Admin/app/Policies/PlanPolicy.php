<?php

namespace Modules\Admin\app\Policies;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $admin): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function view(User $admin, Plan $plan): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function create(User $admin): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $admin, Plan $plan): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(User $admin, Plan $plan): bool
    {
        return $admin->hasRole('super_admin');
    }

    public function toggleStatus(User $admin, Plan $plan): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin']);
    }
}
