<?php

namespace Modules\Admin\app\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlanRepositoryInterface extends BaseRepositoryInterface
{
    public function getByType(string $type): Collection;

    public function paginateByType(string $type, ?string $search = null, int $perPage = 20): LengthAwarePaginator;

    public function getActive(): Collection;

    public function countActive(): int;

    public function countTotal(): int;
}
