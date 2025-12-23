<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPlansTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_plans_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/plans');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_plan(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/plans', [
                'name' => 'Basic Plan',
                'type' => 'retailer',
                'price' => 99.99,
                'offers_count' => 10,
                'duration_months' => 1,
                'billing_period' => 'monthly',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Basic Plan',
            'type' => 'retailer',
            'price' => 99.99,
        ]);
    }

    public function test_admin_can_update_plan(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Original Plan',
            'type' => 'retailer',
            'price' => 50.00,
            'offers_count' => 5,
            'duration_months' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/plans/{$plan->id}", [
                'name' => 'Updated Plan',
                'type' => 'retailer',
                'price' => 75.00,
                'offers_count' => 10,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', [
            'id' => $plan->id,
            'name' => 'Updated Plan',
            'price' => 75.00,
        ]);
    }

    public function test_admin_can_delete_plan(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'To Delete',
            'type' => 'retailer',
            'price' => 50.00,
            'offers_count' => 5,
            'duration_months' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/plans/{$plan->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('subscription_plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_create_plan_validation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/plans', [
                'type' => 'retailer',
                'price' => 99.99,
                'offers_count' => 10,
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_create_plan_validation_requires_valid_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/plans', [
                'name' => 'Test Plan',
                'type' => 'invalid_type',
                'price' => 99.99,
                'offers_count' => 10,
            ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_filter_plans_by_type(): void
    {
        SubscriptionPlan::create([
            'name' => 'Retailer Plan',
            'type' => 'retailer',
            'price' => 50.00,
            'offers_count' => 5,
            'duration_months' => 1,
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'User Plan',
            'type' => 'user',
            'price' => 25.00,
            'offers_count' => 3,
            'duration_months' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/plans?type=retailer');

        $response->assertStatus(200);
    }
}
