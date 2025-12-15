<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Create and authenticate admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_users_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'role' => 'customer',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'name' => 'New User',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
        ]);
        $user->assignRole('customer');

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'role' => 'customer',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_create_user_validation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'email' => 'test@test.com',
                'password' => 'password123',
                'role' => 'customer',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_create_user_validation_requires_unique_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@test.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'existing@test.com',
                'password' => 'password123',
                'role' => 'customer',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_unauthenticated_user_cannot_access_users_page(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_search_filters_users(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users?search=John');

        $response->assertStatus(200);
    }
}
