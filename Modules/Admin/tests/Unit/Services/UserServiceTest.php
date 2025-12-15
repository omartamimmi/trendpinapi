<?php

namespace Modules\Admin\Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected MockInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->userService = new UserService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_users_returns_paginated_results(): void
    {
        $this->userRepository
            ->shouldReceive('paginateWithRoles')
            ->once()
            ->with(null, 20)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20));

        $result = $this->userService->getUsers();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_get_users_with_search_term(): void
    {
        $searchTerm = 'john';

        $this->userRepository
            ->shouldReceive('paginateWithRoles')
            ->once()
            ->with($searchTerm, 20)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20));

        $result = $this->userService->getUsers($searchTerm);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_get_user_returns_user(): void
    {
        $user = new User(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);

        $this->userRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->userService->getUser(1);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Test User', $result->name);
    }

    public function test_create_user_creates_and_returns_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $createdUser = Mockery::mock(User::class);
        $createdUser->shouldReceive('assignRole')->once()->with('admin');

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdUser);

        $result = $this->userService->createUser($userData);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_update_user_updates_user_data(): void
    {
        $userId = 1;
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator',
        ];

        $existingUser = Mockery::mock(User::class);
        $existingUser->shouldReceive('syncRoles')->once()->with(['moderator']);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($userId, Mockery::type('array'))
            ->andReturn($existingUser);

        $result = $this->userService->updateUser($userId, $updateData);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_update_user_with_password_hashes_password(): void
    {
        $userId = 1;
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
            'role' => 'admin',
        ];

        $existingUser = Mockery::mock(User::class);
        $existingUser->shouldReceive('syncRoles')->once();

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($userId, Mockery::on(function ($data) {
                return isset($data['password']) && Hash::check('newpassword', $data['password']);
            }))
            ->andReturn($existingUser);

        $result = $this->userService->updateUser($userId, $updateData);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_delete_user_returns_true(): void
    {
        $userId = 1;

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with($userId)
            ->andReturn(true);

        $result = $this->userService->deleteUser($userId);

        $this->assertTrue($result);
    }

    public function test_get_retailers_returns_paginated_results(): void
    {
        $this->userRepository
            ->shouldReceive('getRetailers')
            ->once()
            ->with(null, 20)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20));

        $result = $this->userService->getRetailers();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }
}
