<?php

namespace Modules\Admin\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $admin): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function view(User $admin, User $user): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }

    public function create(User $admin): bool
    {
        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $admin, User $user): bool
    {
        if ($admin->id === $user->id) {
            return true;
        }

        if ($user->hasRole('super_admin') && !$admin->hasRole('super_admin')) {
            return false;
        }

        return $admin->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(User $admin, User $user): bool
    {
        if ($admin->id === $user->id) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $admin->hasRole('super_admin');
    }

    public function assignRole(User $admin, User $user): bool
    {
        if ($user->hasRole('super_admin') && !$admin->hasRole('super_admin')) {
            return false;
        }

        return $admin->hasRole('super_admin');
    }
}
