<?php

namespace Modules\Geofence\Services;

use Modules\Geofence\Services\Contracts\ThrottleServiceInterface;
use Modules\Geofence\Repositories\Contracts\ThrottleLogRepositoryInterface;
use Modules\Geofence\DTO\ThrottleConfigDTO;
use Carbon\Carbon;

class ThrottleService implements ThrottleServiceInterface
{
    private ThrottleConfigDTO $config;

    public function __construct(
        private ThrottleLogRepositoryInterface $throttleLogRepository
    ) {
        $this->config = ThrottleConfigDTO::fromConfig();
    }

    /**
     * Check if notification can be sent to user
     * Returns null if allowed, or reason string if throttled
     */
    public function canSendNotification(
        int $userId,
        ?int $brandId = null,
        ?int $branchId = null,
        ?int $offerId = null
    ): ?string {
        // Check quiet hours first
        if ($this->isQuietHours()) {
            return 'quiet_hours';
        }

        // Check daily limit
        if ($this->getRemainingToday($userId) <= 0) {
            return 'daily_limit_exceeded';
        }

        // Check weekly limit
        if ($this->getRemainingThisWeek($userId) <= 0) {
            return 'weekly_limit_exceeded';
        }

        // Check minimum interval between any notifications
        $lastNotification = $this->throttleLogRepository->getLastNotificationTime($userId);
        if ($lastNotification) {
            $minutesSinceLastNotification = $lastNotification->diffInMinutes(now());
            if ($minutesSinceLastNotification < $this->config->minIntervalMinutes) {
                return 'min_interval_not_met';
            }
        }

        // Check brand cooldown
        if ($brandId) {
            $lastBrandNotification = $this->throttleLogRepository->getLastNotificationTime($userId, $brandId);
            if ($lastBrandNotification) {
                $hoursSinceLastBrand = $lastBrandNotification->diffInHours(now());
                if ($hoursSinceLastBrand < $this->config->brandCooldownHours) {
                    return 'brand_cooldown';
                }
            }
        }

        // Check location cooldown
        if ($branchId) {
            $lastLocationNotification = $this->throttleLogRepository->getLastNotificationTime(
                $userId,
                null,
                $branchId
            );
            if ($lastLocationNotification) {
                $hoursSinceLastLocation = $lastLocationNotification->diffInHours(now());
                if ($hoursSinceLastLocation < $this->config->locationCooldownHours) {
                    return 'location_cooldown';
                }
            }
        }

        // Check offer cooldown
        if ($offerId) {
            $lastOfferNotification = $this->throttleLogRepository->getLastNotificationTime(
                $userId,
                null,
                null,
                $offerId
            );
            if ($lastOfferNotification) {
                $hoursSinceLastOffer = $lastOfferNotification->diffInHours(now());
                if ($hoursSinceLastOffer < $this->config->offerCooldownHours) {
                    return 'offer_cooldown';
                }
            }
        }

        return null; // No throttling needed
    }

    /**
     * Get the throttle configuration
     */
    public function getConfig(): ThrottleConfigDTO
    {
        return $this->config;
    }

    /**
     * Check if currently in quiet hours
     */
    public function isQuietHours(): bool
    {
        return $this->config->isQuietHours();
    }

    /**
     * Get remaining notifications for today
     */
    public function getRemainingToday(int $userId): int
    {
        $sentToday = $this->throttleLogRepository->getNotificationCountSince(
            $userId,
            today()
        );

        return max(0, $this->config->maxPerDay - $sentToday);
    }

    /**
     * Get remaining notifications for this week
     */
    public function getRemainingThisWeek(int $userId): int
    {
        $sentThisWeek = $this->throttleLogRepository->getNotificationCountSince(
            $userId,
            now()->startOfWeek()
        );

        return max(0, $this->config->maxPerWeek - $sentThisWeek);
    }
}
