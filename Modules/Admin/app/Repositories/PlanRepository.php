<?php

namespace Modules\Admin\app\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(SubscriptionPlan $model)
    {
        parent::__construct($model);
    }

    public function getByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }

    public function paginateByType(string $type, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->where('type', $type);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function countActive(): int
    {
        return $this->model->where('is_active', true)->count();
    }

    public function countTotal(): int
    {
        return $this->model->count();
    }

    public function getActiveByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->where('is_active', true)
            ->get();
    }
}
