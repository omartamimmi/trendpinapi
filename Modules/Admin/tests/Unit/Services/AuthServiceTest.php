<?php

namespace Modules\Admin\Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Modules\Admin\app\Exceptions\InvalidCredentialsException;
use Modules\Admin\app\Exceptions\UnauthorizedAccessException;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\AuthService;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;
    protected MockInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_throws_exception_for_invalid_email(): void
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('nonexistent@example.com')
            ->andReturn(null);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login($credentials);
    }

    public function test_login_throws_exception_for_wrong_password(): void
    {
        $credentials = [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ];

        $user = Mockery::mock(User::class);
        $user->password = Hash::make('correctpassword');

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('admin@example.com')
            ->andReturn($user);

        $this->expectException(InvalidCredentialsException::class);

        $this->authService->login($credentials);
    }

    public function test_login_throws_exception_for_non_admin_user(): void
    {
        $credentials = [
            'email' => 'user@example.com',
            'password' => 'password123',
        ];

        $user = Mockery::mock(User::class);
        $user->password = Hash::make('password123');
        $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('user@example.com')
            ->andReturn($user);

        $this->expectException(UnauthorizedAccessException::class);
        $this->expectExceptionMessage('Access denied. Admin role required.');

        $this->authService->login($credentials);
    }

    public function test_login_returns_data_for_valid_admin(): void
    {
        $credentials = [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];

        $token = Mockery::mock();
        $token->plainTextToken = 'test-token';

        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->email = 'admin@example.com';
        $user->password = Hash::make('password123');
        $user->shouldReceive('hasRole')->with('admin')->andReturn(true);
        $user->shouldReceive('createToken')->with('admin-token')->andReturn($token);
        $user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('admin@example.com')
            ->andReturn($user);

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertEquals('test-token', $result['token']);
    }

    public function test_logout_deletes_current_access_token(): void
    {
        $token = Mockery::mock();
        $token->shouldReceive('delete')->once();

        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->email = 'admin@example.com';
        $user->shouldReceive('currentAccessToken')->andReturn($token);

        $result = $this->authService->logout($user);

        $this->assertTrue($result);
    }

    public function test_get_current_user_returns_user_data(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));
        $user->shouldReceive('getAllPermissions')->andReturn(collect([
            (object)['name' => 'manage-users'],
            (object)['name' => 'manage-plans'],
        ]));

        $result = $this->authService->getCurrentUser($user);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertArrayHasKey('permissions', $result);
    }
}
