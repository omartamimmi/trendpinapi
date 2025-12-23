<?php

namespace Modules\Admin\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Modules\Admin\app\Repositories\Contracts\OnboardingRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\PlanRepositoryInterface;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\DashboardService;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $dashboardService;
    protected MockInterface $userRepository;
    protected MockInterface $planRepository;
    protected MockInterface $onboardingRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->planRepository = Mockery::mock(PlanRepositoryInterface::class);
        $this->onboardingRepository = Mockery::mock(OnboardingRepositoryInterface::class);

        $this->dashboardService = new DashboardService(
            $this->userRepository,
            $this->planRepository,
            $this->onboardingRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_stats_returns_expected_structure(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->userRepository->shouldReceive('all->count')->andReturn(100);
        $this->userRepository->shouldReceive('countThisMonth')->andReturn(15);
        $this->userRepository->shouldReceive('countByRole')->with('retailer')->andReturn(50);

        $this->planRepository->shouldReceive('all->count')->andReturn(5);
        $this->planRepository->shouldReceive('countActive')->andReturn(4);

        $this->onboardingRepository->shouldReceive('countByStatus')->with('pending')->andReturn(3);
        $this->onboardingRepository->shouldReceive('countByStatus')->with('in_progress')->andReturn(2);
        $this->onboardingRepository->shouldReceive('countByStatus')->with('completed')->andReturn(10);

        $result = $this->dashboardService->getStats();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('onboardings', $result);
        $this->assertArrayHasKey('plans', $result);
    }

    public function test_get_stats_returns_correct_user_counts(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $this->userRepository->shouldReceive('all->count')->andReturn(100);
        $this->userRepository->shouldReceive('countThisMonth')->andReturn(15);
        $this->userRepository->shouldReceive('countByRole')->with('retailer')->andReturn(50);

        $this->planRepository->shouldReceive('all->count')->andReturn(5);
        $this->planRepository->shouldReceive('countActive')->andReturn(4);

        $this->onboardingRepository->shouldReceive('countByStatus')->with('pending')->andReturn(3);
        $this->onboardingRepository->shouldReceive('countByStatus')->with('in_progress')->andReturn(2);
        $this->onboardingRepository->shouldReceive('countByStatus')->with('completed')->andReturn(10);

        $result = $this->dashboardService->getStats();

        $this->assertEquals(100, $result['users']['total']);
        $this->assertEquals(15, $result['users']['this_month']);
    }

    public function test_clear_stats_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('admin.dashboard.stats');

        $this->dashboardService->clearStatsCache();

        $this->assertTrue(true);
    }
}
