<?php

namespace Modules\Log\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Log\app\Http\Requests\GetLogsRequest;
use Modules\Log\app\Http\Resources\LogCollection;
use Modules\Log\app\Http\Resources\LogResource;
use Modules\Log\app\Http\Resources\LogStatsResource;
use Modules\Log\app\Services\Contracts\LogServiceInterface;

class AdminLogController extends Controller
{
    public function __construct(
        protected LogServiceInterface $logService
    ) {}

    /**
     * Get paginated logs with filters
     */
    public function index(GetLogsRequest $request): LogCollection
    {
        $logs = $this->logService->getLogs(
            $request->filters(),
            $request->perPage()
        );

        return new LogCollection($logs);
    }

    /**
     * Get a single log entry
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->logService->getLog($id);

        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }

        return response()->json(new LogResource($log));
    }

    /**
     * Get logs for a specific request (correlated)
     */
    public function requestLogs(string $requestId): JsonResponse
    {
        $logs = $this->logService->getRequestLogs($requestId);

        return response()->json([
            'request_id' => $requestId,
            'logs' => LogResource::collection($logs),
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');

        $stats = $this->logService->getDashboardStats($period);

        return response()->json(new LogStatsResource($stats));
    }

    /**
     * Get recent errors for quick view
     */
    public function recentErrors(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 10), 50);

        $errors = $this->logService->getRecentErrors($limit);

        return response()->json([
            'errors' => LogResource::collection($errors),
        ]);
    }

    /**
     * Get activity logs for a specific user
     */
    public function userActivity(int $userId, Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 50), 100);

        $logs = $this->logService->getUserActivity($userId, $limit);

        return response()->json([
            'user_id' => $userId,
            'logs' => LogResource::collection($logs),
        ]);
    }

    /**
     * Get available filter options
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json($this->logService->getFilterOptions());
    }

    /**
     * Manually trigger log cleanup (for testing/admin purposes)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $daysToKeep = $request->input('days', 30);

        $deleted = $this->logService->cleanup($daysToKeep);

        return response()->json([
            'message' => "Deleted {$deleted} old log entries",
            'deleted_count' => $deleted,
        ]);
    }
}
