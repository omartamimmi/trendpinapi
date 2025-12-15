<?php

namespace Modules\Admin\app\Services\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function getUsers(?string $search = null): LengthAwarePaginator;

    public function getRetailers(?string $search = null): LengthAwarePaginator;

    public function getUser(int $id): User;

    public function createUser(array $data): User;

    public function updateUser(int $id, array $data): User;

    public function deleteUser(int $id): bool;

    public function createRetailer(array $data): User;
}
