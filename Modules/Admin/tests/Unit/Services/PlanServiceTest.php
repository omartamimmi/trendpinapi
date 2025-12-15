<?php

namespace Modules\Admin\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\Admin\app\Services\PlanService;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlanService $planService;
    protected MockInterface $planRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planRepository = Mockery::mock(PlanRepositoryInterface::class);
        $this->planService = new PlanService($this->planRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_plans_returns_paginated_results(): void
    {
        $this->planRepository
            ->shouldReceive('paginateByType')
            ->once()
            ->with('retailer', null, 20)
            ->andReturn(new LengthAwarePaginator([], 0, 20));

        $result = $this->planService->getPlans('retailer');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_get_plans_with_search(): void
    {
        $this->planRepository
            ->shouldReceive('paginateByType')
            ->once()
            ->with('retailer', 'basic', 20)
            ->andReturn(new LengthAwarePaginator([], 0, 20));

        $result = $this->planService->getPlans('retailer', 'basic');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_create_plan_with_defaults(): void
    {
        $inputData = [
            'name' => 'Basic Plan',
            'type' => 'retailer',
            'price' => 99.99,
            'offers_count' => 10,
        ];

        $expectedData = array_merge($inputData, [
            'duration_months' => 1,
            'billing_period' => 'monthly',
            'trial_days' => 0,
            'is_active' => true,
        ]);

        $plan = new SubscriptionPlan($expectedData);

        $this->planRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($expectedData) {
                return $data['name'] === $expectedData['name']
                    && $data['duration_months'] === 1
                    && $data['is_active'] === true;
            }))
            ->andReturn($plan);

        $result = $this->planService->createPlan($inputData);

        $this->assertEquals('Basic Plan', $result->name);
    }

    public function test_update_plan(): void
    {
        $planId = 1;
        $updateData = [
            'name' => 'Updated Plan',
            'price' => 149.99,
        ];

        $plan = new SubscriptionPlan($updateData);

        $this->planRepository
            ->shouldReceive('update')
            ->once()
            ->with($planId, $updateData)
            ->andReturn($plan);

        $result = $this->planService->updatePlan($planId, $updateData);

        $this->assertEquals('Updated Plan', $result->name);
    }

    public function test_delete_plan(): void
    {
        $planId = 1;

        $this->planRepository
            ->shouldReceive('delete')
            ->once()
            ->with($planId)
            ->andReturn(true);

        $result = $this->planService->deletePlan($planId);

        $this->assertTrue($result);
    }

    public function test_get_plan(): void
    {
        $planId = 1;
        $plan = new SubscriptionPlan(['id' => 1, 'name' => 'Test Plan']);

        $this->planRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with($planId)
            ->andReturn($plan);

        $result = $this->planService->getPlan($planId);

        $this->assertEquals('Test Plan', $result->name);
    }
}
