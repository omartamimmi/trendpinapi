<?php

namespace Modules\User\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\User\Repositories\Contracts\InterestRepositoryInterface;
use Modules\User\Services\Contracts\InterestServiceInterface;

class InterestService implements InterestServiceInterface
{
    public function __construct(
        private readonly InterestRepositoryInterface $interestRepository
    ) {}

    /**
     * Get all active interests
     */
    public function getAllInterests(): Collection
    {
        return $this->interestRepository->getAllActive();
    }

    /**
     * Get authenticated user's interests
     */
    public function getAuthUserInterests(): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        return $this->interestRepository->getUserInterests($userId);
    }

    /**
     * Set user interests (replaces existing)
     */
    public function setUserInterests(array $interestIds): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        // Validate and get only active interest IDs
        $validInterestIds = $this->getValidInterestIds($interestIds);

        $this->interestRepository->syncUserInterests($userId, $validInterestIds);

        return $this->interestRepository->getUserInterests($userId);
    }

    /**
     * Add interests to user
     */
    public function addUserInterests(array $interestIds): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        // Validate and get only active interest IDs
        $validInterestIds = $this->getValidInterestIds($interestIds);

        $this->interestRepository->addUserInterests($userId, $validInterestIds);

        return $this->interestRepository->getUserInterests($userId);
    }

    /**
     * Remove interests from user
     */
    public function removeUserInterests(array $interestIds): Collection
    {
        $userId = Auth::id();

        if (!$userId) {
            return collect();
        }

        $this->interestRepository->removeUserInterests($userId, $interestIds);

        return $this->interestRepository->getUserInterests($userId);
    }

    /**
     * Filter and return only valid (existing and active) interest IDs
     */
    private function getValidInterestIds(array $interestIds): array
    {
        $validInterests = $this->interestRepository->findByIds($interestIds);

        return $validInterests->pluck('id')->toArray();
    }
}
