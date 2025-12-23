<?php

namespace Modules\Admin\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'roles',
                ],
            ]);
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_non_admin_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_admin_can_get_current_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'roles',
                    'permissions',
                ],
            ]);
    }

    public function test_login_validation_requires_email(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_requires_password(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@test.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
