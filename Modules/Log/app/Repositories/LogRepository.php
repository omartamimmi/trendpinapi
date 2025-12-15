<?php

namespace Modules\Log\app\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Log\app\Models\ActivityLog;
use Modules\Log\app\Repositories\Contracts\LogRepositoryInterface;
use Carbon\Carbon;

class LogRepository implements LogRepositoryInterface
{
    public function __construct(
        protected ActivityLog $model
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['level'])) {
            $query->level($filters['level']);
        }

        if (!empty($filters['min_level'])) {
            $query->minLevel($filters['min_level']);
        }

        if (!empty($filters['channel'])) {
            $query->channel($filters['channel']);
        }

        if (!empty($filters['user_id'])) {
            $query->fromUser($filters['user_id']);
        }

        if (!empty($filters['user_type'])) {
            $query->fromUserType($filters['user_type']);
        }

        if (!empty($filters['ip_address'])) {
            $query->fromIp($filters['ip_address']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('logged_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('logged_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['has_exception'])) {
            $query->withExceptions();
        }

        if (!empty($filters['request_id'])) {
            $query->requestId($filters['request_id']);
        }

        // Default ordering by most recent
        $sortField = $filters['sort_by'] ?? 'logged_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Eager load user relationship selectively
        $query->with(['user:id,name,email']);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?ActivityLog
    {
        return $this->model->with('user:id,name,email')->find($id);
    }

    public function getByRequestId(string $requestId): Collection
    {
        return $this->model
            ->where('request_id', $requestId)
            ->orderBy('logged_at', 'asc')
            ->get();
    }

    public function getStatistics(string $period = 'today'): array
    {
        $dateRange = $this->getDateRange($period);

        $query = $this->model->whereBetween('logged_at', $dateRange);

        return [
            'total' => (clone $query)->count(),
            'errors' => (clone $query)->whereIn('level', ['error', 'critical', 'alert', 'emergency'])->count(),
            'warnings' => (clone $query)->where('level', 'warning')->count(),
            'info' => (clone $query)->where('level', 'info')->count(),
            'debug' => (clone $query)->where('level', 'debug')->count(),
            'unique_users' => (clone $query)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'unique_ips' => (clone $query)->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
            'with_exceptions' => (clone $query)->whereNotNull('exception_class')->count(),
        ];
    }

    public function getLevelCounts(string $period = 'today'): array
    {
        $dateRange = $this->getDateRange($period);

        return $this->model
            ->whereBetween('logged_at', $dateRange)
            ->select('level', DB::raw('COUNT(*) as count'))
            ->groupBy('level')
            ->pluck('count', 'level')
            ->toArray();
    }

    public function getChannelCounts(string $period = 'today'): array
    {
        $dateRange = $this->getDateRange($period);

        return $this->model
            ->whereBetween('logged_at', $dateRange)
            ->select('channel', DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->orderByDesc('count')
            ->pluck('count', 'channel')
            ->toArray();
    }

    public function getRecentErrors(int $limit = 10): Collection
    {
        return $this->model
            ->whereIn('level', ['error', 'critical', 'alert', 'emergency'])
            ->with('user:id,name,email')
            ->orderByDesc('logged_at')
            ->limit($limit)
            ->get();
    }

    public function getByUser(int $userId, int $limit = 50): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderByDesc('logged_at')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): ActivityLog
    {
        return $this->model->create($data);
    }

    public function bulkInsert(array $logs): bool
    {
        // Chunk for better performance with large datasets
        $chunks = array_chunk($logs, 1000);

        foreach ($chunks as $chunk) {
            $this->model->insert($chunk);
        }

        return true;
    }

    public function deleteOlderThan(string $date): int
    {
        // Use chunked deletion for better performance
        $totalDeleted = 0;

        do {
            $deleted = $this->model
                ->where('logged_at', '<', $date)
                ->limit(1000)
                ->delete();

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    public function getUniqueChannels(): array
    {
        return $this->model
            ->select('channel')
            ->distinct()
            ->pluck('channel')
            ->toArray();
    }

    public function getTopExceptions(int $limit = 10, string $period = 'today'): Collection
    {
        $dateRange = $this->getDateRange($period);

        return $this->model
            ->whereBetween('logged_at', $dateRange)
            ->whereNotNull('exception_class')
            ->select('exception_class', 'exception_message', DB::raw('COUNT(*) as count'), DB::raw('MAX(logged_at) as last_occurrence'))
            ->groupBy('exception_class', 'exception_message')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get timeline data for charts
     */
    public function getTimeline(string $period = 'today', string $groupBy = 'hour'): Collection
    {
        $dateRange = $this->getDateRange($period);

        $format = match ($groupBy) {
            'minute' => '%Y-%m-%d %H:%i',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            default => '%Y-%m-%d %H:00',
        };

        return $this->model
            ->whereBetween('logged_at', $dateRange)
            ->select(
                DB::raw("DATE_FORMAT(logged_at, '{$format}') as time_bucket"),
                'level',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('time_bucket', 'level')
            ->orderBy('time_bucket')
            ->get();
    }

    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week' => [$now->copy()->subWeek(), $now],
            'month' => [$now->copy()->subMonth(), $now],
            'year' => [$now->copy()->subYear(), $now],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
