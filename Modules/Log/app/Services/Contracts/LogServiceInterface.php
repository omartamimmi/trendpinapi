<?php

namespace Modules\Log\app\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Log\app\Models\ActivityLog;

interface LogServiceInterface
{
    /**
     * Get paginated logs with filters
     */
    public function getLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator;

    /**
     * Get a single log entry
     */
    public function getLog(int $id): ?ActivityLog;

    /**
     * Get logs for a request (correlated by request ID)
     */
    public function getRequestLogs(string $requestId): Collection;

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(string $period = 'today'): array;

    /**
     * Get recent errors for quick view
     */
    public function getRecentErrors(int $limit = 10): Collection;

    /**
     * Get activity logs for a specific user
     */
    public function getUserActivity(int $userId, int $limit = 50): Collection;

    /**
     * Log an entry (called by custom log channel)
     */
    public function log(
        string $level,
        string $message,
        string $channel = 'application',
        array $context = [],
        array $extra = []
    ): ActivityLog;

    /**
     * Clean up old logs
     */
    public function cleanup(int $daysToKeep = 30): int;

    /**
     * Get available filters options (channels, levels, etc.)
     */
    public function getFilterOptions(): array;
}
