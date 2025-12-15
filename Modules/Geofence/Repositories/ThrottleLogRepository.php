<?php

namespace Modules\Geofence\Repositories;

use Modules\Geofence\app\Models\NotificationThrottleLog;
use Modules\Geofence\Repositories\Contracts\ThrottleLogRepositoryInterface;

class ThrottleLogRepository implements ThrottleLogRepositoryInterface
{
    public function __construct(
        protected NotificationThrottleLog $model
    ) {}

    public function create(array $data): NotificationThrottleLog
    {
        return $this->model->create(array_merge($data, [
            'sent_at' => $data['sent_at'] ?? now(),
        ]));
    }

    public function createSent(array $data): NotificationThrottleLog
    {
        return $this->create(array_merge($data, [
            'status' => NotificationThrottleLog::STATUS_SENT,
        ]));
    }

    public function createThrottled(array $data, string $reason): NotificationThrottleLog
    {
        return $this->create(array_merge($data, [
            'status' => NotificationThrottleLog::STATUS_THROTTLED,
            'skip_reason' => $reason,
        ]));
    }

    public function createSkipped(array $data, string $reason): NotificationThrottleLog
    {
        return $this->create(array_merge($data, [
            'status' => NotificationThrottleLog::STATUS_SKIPPED,
            'skip_reason' => $reason,
        ]));
    }

    public function countSentToday(int $userId): int
    {
        return $this->model->forUser($userId)
            ->sent()
            ->sentToday()
            ->count();
    }

    public function countSentThisWeek(int $userId): int
    {
        return $this->model->forUser($userId)
            ->sent()
            ->sentThisWeek()
            ->count();
    }

    public function getLastSentForUser(int $userId): ?NotificationThrottleLog
    {
        return $this->model->forUser($userId)
            ->sent()
            ->orderByDesc('sent_at')
            ->first();
    }

    public function getLastSentForBrand(int $userId, int $brandId): ?NotificationThrottleLog
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('brand_id', $brandId)
            ->orderByDesc('sent_at')
            ->first();
    }

    public function getLastSentForBranch(int $userId, int $branchId): ?NotificationThrottleLog
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('branch_id', $branchId)
            ->orderByDesc('sent_at')
            ->first();
    }

    public function getLastSentForOffer(int $userId, int $offerId): ?NotificationThrottleLog
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('offer_id', $offerId)
            ->orderByDesc('sent_at')
            ->first();
    }

    public function hasRecentNotification(int $userId, int $minutes): bool
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('sent_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    public function hasBrandCooldown(int $userId, int $brandId, int $hours): bool
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('brand_id', $brandId)
            ->sentWithinHours($hours)
            ->exists();
    }

    public function hasBranchCooldown(int $userId, int $branchId, int $hours): bool
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('branch_id', $branchId)
            ->sentWithinHours($hours)
            ->exists();
    }

    public function hasOfferCooldown(int $userId, int $offerId, int $hours): bool
    {
        return $this->model->forUser($userId)
            ->sent()
            ->where('offer_id', $offerId)
            ->sentWithinHours($hours)
            ->exists();
    }

    /**
     * Get recent notification logs
     */
    public function getRecentLogs(int $limit = 10, ?int $userId = null): \Illuminate\Support\Collection
    {
        $query = $this->model
            ->with(['user', 'brand', 'branch', 'offer']);

        if ($userId) {
            $query->forUser($userId);
        }

        return $query
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification count since a date
     */
    public function getNotificationCountSince(int $userId, \Carbon\Carbon $since): int
    {
        return $this->model
            ->forUser($userId)
            ->sent()
            ->where('sent_at', '>=', $since)
            ->count();
    }

    /**
     * Get last notification time for a user
     */
    public function getLastNotificationTime(
        int $userId,
        ?int $brandId = null,
        ?int $branchId = null,
        ?int $offerId = null
    ): ?\Carbon\Carbon {
        $query = $this->model->forUser($userId)->sent();

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($offerId) {
            $query->where('offer_id', $offerId);
        }

        $log = $query->orderByDesc('sent_at')->first();

        return $log?->sent_at;
    }
}
