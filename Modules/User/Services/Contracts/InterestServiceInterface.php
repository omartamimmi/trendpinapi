<?php

namespace Modules\User\Services\Contracts;

use Illuminate\Support\Collection;

interface InterestServiceInterface
{
    /**
     * Get all active interests
     */
    public function getAllInterests(): Collection;

    /**
     * Get authenticated user's interests
     */
    public function getAuthUserInterests(): Collection;

    /**
     * Set user interests (replaces existing)
     */
    public function setUserInterests(array $interestIds): Collection;

    /**
     * Add interests to user
     */
    public function addUserInterests(array $interestIds): Collection;

    /**
     * Remove interests from user
     */
    public function removeUserInterests(array $interestIds): Collection;
}
