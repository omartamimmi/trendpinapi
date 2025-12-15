<?php

namespace Modules\Log\app\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Log\app\Models\ActivityLog;

interface LogRepositoryInterface
{
    /**
     * Get paginated logs with filters
     */
    public function getPaginated(array $filters = [], int $perPage = 50): LengthAwarePaginator;

    /**
     * Get a single log entry by ID
     */
    public function findById(int $id): ?ActivityLog;

    /**
     * Get logs by request ID (for request correlation)
     */
    public function getByRequestId(string $requestId): Collection;

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(string $period = 'today'): array;

    /**
     * Get log level counts
     */
    public function getLevelCounts(string $period = 'today'): array;

    /**
     * Get channel counts
     */
    public function getChannelCounts(string $period = 'today'): array;

    /**
     * Get recent errors
     */
    public function getRecentErrors(int $limit = 10): Collection;

    /**
     * Get logs by user
     */
    public function getByUser(int $userId, int $limit = 50): Collection;

    /**
     * Create a new log entry
     */
    public function create(array $data): ActivityLog;

    /**
     * Bulk insert logs (for performance)
     */
    public function bulkInsert(array $logs): bool;

    /**
     * Delete logs older than given date
     */
    public function deleteOlderThan(string $date): int;

    /**
     * Get unique channels
     */
    public function getUniqueChannels(): array;

    /**
     * Get top error types
     */
    public function getTopExceptions(int $limit = 10, string $period = 'today'): Collection;
}
