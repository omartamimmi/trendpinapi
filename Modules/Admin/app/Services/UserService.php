<?php

namespace Modules\Admin\app\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\app\Events\UserCreated;
use Modules\Admin\app\Events\UserDeleted;
use Modules\Admin\app\Events\UserUpdated;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;
use Modules\Admin\app\Services\Contracts\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getUsers(?string $search = null): LengthAwarePaginator
    {
        $perPage = config('admin.pagination.per_page', 20);
        return $this->userRepository->paginateWithRoles($search, $perPage);
    }

    public function getRetailers(?string $search = null): LengthAwarePaginator
    {
        $perPage = config('admin.pagination.per_page', 20);
        return $this->userRepository->getRetailers($search, $perPage);
    }

    public function getUser(int $id): User
    {
        return $this->userRepository->findWithRelations($id, ['roles', 'retailerOnboarding']);
    }

    public function createUser(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
        ];

        $user = $this->userRepository->create($userData);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        event(new UserCreated($user));

        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        $user = $this->userRepository->update($id, $updateData);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        event(new UserUpdated($user));

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findOrFail($id);
        $result = $this->userRepository->delete($id);

        if ($result) {
            event(new UserDeleted($user));
        }

        return $result;
    }

    public function createRetailer(array $data): User
    {
        $data['role'] = 'retailer';
        return $this->createUser($data);
    }

    public function getUserCount(): int
    {
        return $this->userRepository->all()->count();
    }

    public function getUserCountThisMonth(): int
    {
        return $this->userRepository->countThisMonth();
    }
}
