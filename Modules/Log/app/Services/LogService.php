<?php

namespace Modules\Log\app\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Modules\Log\app\Models\ActivityLog;
use Modules\Log\app\Repositories\Contracts\LogRepositoryInterface;
use Modules\Log\app\Services\Contracts\LogServiceInterface;
use Carbon\Carbon;

class LogService implements LogServiceInterface
{
    public function __construct(
        protected LogRepositoryInterface $repository
    ) {}

    public function getLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function getLog(int $id): ?ActivityLog
    {
        return $this->repository->findById($id);
    }

    public function getRequestLogs(string $requestId): Collection
    {
        return $this->repository->getByRequestId($requestId);
    }

    public function getDashboardStats(string $period = 'today'): array
    {
        return [
            'summary' => $this->repository->getStatistics($period),
            'by_level' => $this->repository->getLevelCounts($period),
            'by_channel' => $this->repository->getChannelCounts($period),
            'top_exceptions' => $this->repository->getTopExceptions(5, $period),
            'timeline' => $this->repository->getTimeline($period, $this->getTimelineGroupBy($period)),
        ];
    }

    public function getRecentErrors(int $limit = 10): Collection
    {
        return $this->repository->getRecentErrors($limit);
    }

    public function getUserActivity(int $userId, int $limit = 50): Collection
    {
        return $this->repository->getByUser($userId, $limit);
    }

    public function log(
        string $level,
        string $message,
        string $channel = 'application',
        array $context = [],
        array $extra = []
    ): ActivityLog {
        $data = [
            'level' => $level,
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
            'extra' => $extra,
            'logged_at' => now(),
        ];

        // Add request context if available
        if ($request = Request::instance()) {
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['request_method'] = $request->method();
            $data['request_url'] = $request->fullUrl();
            $data['request_id'] = $request->header('X-Request-ID') ?? $context['request_id'] ?? null;
        }

        // Add user context if authenticated
        if ($user = Auth::user()) {
            $data['user_id'] = $user->id;
            $data['user_type'] = $this->determineUserType($user);
        }

        // Extract exception data if present
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
            $data['exception_class'] = get_class($exception);
            $data['exception_message'] = $exception->getMessage();
            $data['exception_trace'] = $exception->getTraceAsString();
            $data['exception_file'] = $exception->getFile();
            $data['exception_line'] = $exception->getLine();

            // Remove the exception object from context to avoid serialization issues
            unset($context['exception']);
            $data['context'] = $context;
        }

        // Add performance metrics if available
        if (defined('LARAVEL_START')) {
            $data['duration_ms'] = (microtime(true) - LARAVEL_START) * 1000;
        }
        $data['memory_usage'] = memory_get_usage(true);

        return $this->repository->create($data);
    }

    public function cleanup(int $daysToKeep = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep)->toDateTimeString();
        return $this->repository->deleteOlderThan($cutoffDate);
    }

    public function getFilterOptions(): array
    {
        return [
            'levels' => array_keys(ActivityLog::LEVELS),
            'channels' => $this->repository->getUniqueChannels(),
            'user_types' => ['admin', 'retailer', 'customer'],
        ];
    }

    protected function determineUserType($user): string
    {
        if ($user->hasRole('admin')) {
            return 'admin';
        }
        if ($user->hasRole('retailer')) {
            return 'retailer';
        }
        return 'customer';
    }

    protected function getTimelineGroupBy(string $period): string
    {
        return match ($period) {
            'today', 'yesterday' => 'hour',
            'week' => 'day',
            'month', 'year' => 'day',
            default => 'hour',
        };
    }
}
