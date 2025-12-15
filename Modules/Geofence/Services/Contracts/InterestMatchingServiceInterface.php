<?php

namespace Modules\Geofence\Services\Contracts;

use Illuminate\Support\Collection;

interface InterestMatchingServiceInterface
{
    /**
     * Check if user has interests matching the brand's categories
     */
    public function userMatchesBrand(int $userId, int $brandId): bool;

    /**
     * Get matching offers for a user at a brand
     * Filters offers based on user interests
     */
    public function getMatchingOffers(int $userId, int $brandId): Collection;

    /**
     * Get the best offer to notify user about
     * Returns the most relevant offer based on interests and offer value
     */
    public function getBestMatchingOffer(int $userId, int $brandId): ?object;

    /**
     * Get user's interest IDs
     */
    public function getUserInterestIds(int $userId): array;

    /**
     * Get brand's category IDs
     */
    public function getBrandCategoryIds(int $brandId): array;
}
