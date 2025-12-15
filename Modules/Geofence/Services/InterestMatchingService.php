<?php

namespace Modules\Geofence\Services;

use Modules\Geofence\Services\Contracts\InterestMatchingServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InterestMatchingService implements InterestMatchingServiceInterface
{
    /**
     * Check if user has interests matching the brand's categories
     */
    public function userMatchesBrand(int $userId, int $brandId): bool
    {
        $userInterestIds = $this->getUserInterestIds($userId);
        $brandCategoryIds = $this->getBrandCategoryIds($brandId);

        if (empty($userInterestIds) || empty($brandCategoryIds)) {
            return false;
        }

        // Check for overlap between user interests and brand categories
        return count(array_intersect($userInterestIds, $brandCategoryIds)) > 0;
    }

    /**
     * Get matching offers for a user at a brand
     * Returns active offers from brands that match user interests
     */
    public function getMatchingOffers(int $userId, int $brandId): Collection
    {
        // First check if user interests match brand categories
        if (!$this->userMatchesBrand($userId, $brandId)) {
            return collect();
        }

        // Get active offers from the brand
        return DB::table('offers')
            ->where('offers.brand_id', $brandId)
            ->where('offers.status', 'active')
            ->where(function ($query) {
                $query->whereNull('offers.start_date')
                    ->orWhere('offers.start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('offers.end_date')
                    ->orWhere('offers.end_date', '>=', now());
            })
            ->select('offers.*')
            ->orderByDesc('offers.discount_value')
            ->orderByDesc('offers.created_at')
            ->get();
    }

    /**
     * Get the best offer to notify user about
     * Returns the most relevant offer based on interests and offer value
     */
    public function getBestMatchingOffer(int $userId, int $brandId): ?object
    {
        $offers = $this->getMatchingOffers($userId, $brandId);

        if ($offers->isEmpty()) {
            return null;
        }

        // Return the first offer (already sorted by best value)
        return $offers->first();
    }

    /**
     * Get user's interest IDs
     */
    public function getUserInterestIds(int $userId): array
    {
        $cacheKey = "user_interests_{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($userId) {
            return DB::table('interest_user')
                ->where('user_id', $userId)
                ->pluck('interest_id')
                ->toArray();
        });
    }

    /**
     * Get brand's category IDs
     */
    public function getBrandCategoryIds(int $brandId): array
    {
        $cacheKey = "brand_categories_{$brandId}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($brandId) {
            return DB::table('brand_category')
                ->where('brand_id', $brandId)
                ->pluck('category_id')
                ->toArray();
        });
    }
}
