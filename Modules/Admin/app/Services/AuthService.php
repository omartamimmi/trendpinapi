<?php

namespace Modules\Admin\app\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Events\AdminLoggedIn;
use Modules\Admin\app\Events\AdminLoggedOut;
use Modules\Admin\app\Exceptions\InvalidCredentialsException;
use Modules\Admin\app\Exceptions\UnauthorizedAccessException;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\Contracts\AuthServiceInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials');
        }

        if (!$user->hasRole('admin')) {
            throw new UnauthorizedAccessException('Access denied. Admin role required.');
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        event(new AdminLoggedIn($user));

        Log::channel('admin')->info('Admin logged in', [
            'admin_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
        ]);

        return [
            'user' => $user,
            'token' => $token,
            'roles' => $user->getRoleNames(),
        ];
    }

    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();

        event(new AdminLoggedOut($user));

        Log::channel('admin')->info('Admin logged out', [
            'admin_id' => $user->id,
            'email' => $user->email,
        ]);

        return true;
    }

    public function getCurrentUser(User $user): array
    {
        return [
            'user' => $user,
            // 'roles' => $user->getRoleNames(),
            // 'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
