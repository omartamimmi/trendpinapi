<?php

namespace Modules\User\Repositories;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\User\Repositories\Contracts\InterestRepositoryInterface;

class InterestRepository implements InterestRepositoryInterface
{
    private const STATUS_ACTIVE = 1;

    public function __construct(
        protected Interest $model
    ) {}

    /**
     * Get all active interests
     */
    public function getAllActive(): Collection
    {
        return $this->model->newQuery()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get interest by ID
     */
    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * Get interests by IDs (only active ones)
     */
    public function findByIds(array $ids): Collection
    {
        return $this->model->newQuery()
            ->whereIn('id', $ids)
            ->where('status', self::STATUS_ACTIVE)
            ->get();
    }

    /**
     * Get user's interests
     */
    public function getUserInterests(int $userId): Collection
    {
        $user = User::find($userId);

        if (!$user) {
            return collect();
        }

        return $user->interests()->get();
    }

    /**
     * Sync user interests (replace all)
     */
    public function syncUserInterests(int $userId, array $interestIds): void
    {
        $user = User::find($userId);

        if ($user) {
            $user->interests()->sync($interestIds);
        }
    }

    /**
     * Add interests to user (without removing existing)
     */
    public function addUserInterests(int $userId, array $interestIds): void
    {
        $user = User::find($userId);

        if ($user) {
            $user->interests()->syncWithoutDetaching($interestIds);
        }
    }

    /**
     * Remove interests from user
     */
    public function removeUserInterests(int $userId, array $interestIds): void
    {
        $user = User::find($userId);

        if ($user) {
            $user->interests()->detach($interestIds);
        }
    }
}
