<?php

namespace Modules\User\Repositories\Contracts;

use Illuminate\Support\Collection;

interface InterestRepositoryInterface
{
    /**
     * Get all active interests
     */
    public function getAllActive(): Collection;

    /**
     * Get interest by ID
     */
    public function findById(int $id);

    /**
     * Get interests by IDs
     */
    public function findByIds(array $ids): Collection;

    /**
     * Get user's interests
     */
    public function getUserInterests(int $userId): Collection;

    /**
     * Sync user interests (replace all)
     */
    public function syncUserInterests(int $userId, array $interestIds): void;

    /**
     * Add interests to user (without removing existing)
     */
    public function addUserInterests(int $userId, array $interestIds): void;

    /**
     * Remove interests from user
     */
    public function removeUserInterests(int $userId, array $interestIds): void;
}
