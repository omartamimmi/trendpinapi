<?php

namespace Modules\Admin\app\Services\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

interface PlanServiceInterface
{
    public function getPlans(string $type, ?string $search = null): LengthAwarePaginator;

    public function getPlan(int $id): SubscriptionPlan;

    public function createPlan(array $data): SubscriptionPlan;

    public function updatePlan(int $id, array $data): SubscriptionPlan;

    public function deletePlan(int $id): bool;
}
