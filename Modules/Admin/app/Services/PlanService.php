<?php

namespace Modules\Admin\app\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Admin\app\Events\PlanCreated;
use Modules\Admin\app\Events\PlanDeleted;
use Modules\Admin\app\Events\PlanUpdated;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\Admin\app\Services\Contracts\PlanServiceInterface;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class PlanService implements PlanServiceInterface
{
    public function __construct(
        protected PlanRepositoryInterface $planRepository
    ) {}

    public function getPlans(string $type, ?string $search = null): LengthAwarePaginator
    {
        $perPage = config('admin.pagination.per_page', 20);
        return $this->planRepository->paginateByType($type, $search, $perPage);
    }

    public function getPlan(int $id): SubscriptionPlan
    {
        return $this->planRepository->findOrFail($id);
    }

    public function createPlan(array $data): SubscriptionPlan
    {
        $planData = $this->preparePlanData($data);
        $plan = $this->planRepository->create($planData);

        event(new PlanCreated($plan));

        return $plan;
    }

    public function updatePlan(int $id, array $data): SubscriptionPlan
    {
        $planData = $this->preparePlanData($data);
        $plan = $this->planRepository->update($id, $planData);

        event(new PlanUpdated($plan));

        return $plan;
    }

    public function deletePlan(int $id): bool
    {
        $plan = $this->planRepository->findOrFail($id);
        $result = $this->planRepository->delete($id);

        if ($result) {
            event(new PlanDeleted($plan));
        }

        return $result;
    }

    public function getActivePlans(): \Illuminate\Support\Collection
    {
        return $this->planRepository->getActive();
    }

    public function getPlanCount(): int
    {
        return $this->planRepository->countTotal();
    }

    public function getActivePlanCount(): int
    {
        return $this->planRepository->countActive();
    }

    protected function preparePlanData(array $data): array
    {
        return [
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'offers_count' => $data['offers_count'],
            'duration_months' => $data['duration_months'] ?? 1,
            'billing_period' => $data['billing_period'] ?? 'monthly',
            'trial_days' => $data['trial_days'] ?? 0,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }
}
