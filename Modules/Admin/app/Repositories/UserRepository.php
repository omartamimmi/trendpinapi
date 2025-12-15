<?php

namespace Modules\Admin\app\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Admin\app\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->role($role)->get();
    }

    public function paginateWithRoles(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getRetailers(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->role('retailer')->with(['retailerOnboarding']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function countByRole(string $role): int
    {
        return $this->model->role($role)->count();
    }

    public function countThisMonth(): int
    {
        return $this->model->whereMonth('created_at', now()->month)->count();
    }

    public function findWithRelations(int $id, array $relations = []): User
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    public function syncRoles(int $userId, array $roles): void
    {
        $user = $this->findOrFail($userId);
        $user->syncRoles($roles);
    }

    public function assignRole(int $userId, string $role): void
    {
        $user = $this->findOrFail($userId);
        $user->assignRole($role);
    }
}
