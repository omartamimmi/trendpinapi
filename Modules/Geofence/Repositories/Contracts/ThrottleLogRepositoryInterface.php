<?php

namespace Modules\Geofence\Repositories\Contracts;

use Modules\Geofence\app\Models\NotificationThrottleLog;

interface ThrottleLogRepositoryInterface
{
    public function create(array $data): NotificationThrottleLog;

    public function createSent(array $data): NotificationThrottleLog;

    public function createThrottled(array $data, string $reason): NotificationThrottleLog;

    public function createSkipped(array $data, string $reason): NotificationThrottleLog;

    public function countSentToday(int $userId): int;

    public function countSentThisWeek(int $userId): int;

    public function getLastSentForUser(int $userId): ?NotificationThrottleLog;

    public function getLastSentForBrand(int $userId, int $brandId): ?NotificationThrottleLog;

    public function getLastSentForBranch(int $userId, int $branchId): ?NotificationThrottleLog;

    public function getLastSentForOffer(int $userId, int $offerId): ?NotificationThrottleLog;

    public function hasRecentNotification(int $userId, int $minutes): bool;

    public function hasBrandCooldown(int $userId, int $brandId, int $hours): bool;

    public function hasBranchCooldown(int $userId, int $branchId, int $hours): bool;

    public function hasOfferCooldown(int $userId, int $offerId, int $hours): bool;

    /**
     * Get recent notification logs
     */
    public function getRecentLogs(int $limit = 10, ?int $userId = null): \Illuminate\Support\Collection;

    /**
     * Get notification count since a date
     */
    public function getNotificationCountSince(int $userId, \Carbon\Carbon $since): int;

    /**
     * Get last notification time for a user
     */
    public function getLastNotificationTime(
        int $userId,
        ?int $brandId = null,
        ?int $branchId = null,
        ?int $offerId = null
    ): ?\Carbon\Carbon;
}
