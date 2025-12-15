<?php

namespace Modules\Geofence\Services\Contracts;

use Modules\Geofence\DTO\ThrottleConfigDTO;

interface ThrottleServiceInterface
{
    /**
     * Check if notification can be sent to user
     * Returns null if allowed, or reason string if throttled
     */
    public function canSendNotification(
        int $userId,
        ?int $brandId = null,
        ?int $branchId = null,
        ?int $offerId = null
    ): ?string;

    /**
     * Get the throttle configuration
     */
    public function getConfig(): ThrottleConfigDTO;

    /**
     * Check if currently in quiet hours
     */
    public function isQuietHours(): bool;

    /**
     * Get remaining notifications for today
     */
    public function getRemainingToday(int $userId): int;

    /**
     * Get remaining notifications for this week
     */
    public function getRemainingThisWeek(int $userId): int;
}
