<?php

namespace Modules\Admin\app\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

interface OnboardingRepositoryInterface extends BaseRepositoryInterface
{
    public function getByStatus(string $status): Collection;

    public function paginateByStatus(string $status, ?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function findWithRelations(int $id): RetailerOnboarding;

    public function getStatusCounts(): array;

    public function countByStatus(string $status): int;

    public function countTotal(): int;

    public function countInProgress(): int;

    public function countCompleted(): int;
}
