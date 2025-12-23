<?php

namespace Modules\Admin\app\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function getByRole(string $role): Collection;

    public function paginateWithRoles(?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function getRetailers(?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function countByRole(string $role): int;

    public function countThisMonth(): int;
}
