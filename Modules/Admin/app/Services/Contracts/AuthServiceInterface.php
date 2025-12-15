<?php

namespace Modules\Admin\app\Services\Contracts;

use App\Models\User;
use Illuminate\Http\JsonResponse;

interface AuthServiceInterface
{
    public function login(array $credentials): array;
    public function logout(User $user): bool;
    public function getCurrentUser(User $user): array;
}
