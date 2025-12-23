<?php

namespace Modules\Log\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Log\app\Services\Contracts\LogServiceInterface;
use Modules\Log\app\Http\Resources\LogResource;

class AdminLogPageController extends Controller
{
    public function __construct(
        protected LogServiceInterface $logService
    ) {}

    /**
     * Display logs listing page
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'level',
            'channel',
            'search',
            'from_date',
            'to_date',
            'has_exception',
            'user_id',
            'ip_address',
            'request_id',
        ]);

        // Convert has_exception to boolean
        if (isset($filters['has_exception'])) {
            $filters['has_exception'] = filter_var($filters['has_exception'], FILTER_VALIDATE_BOOLEAN);
        }

        $perPage = $request->input('per_page', 10);
        $statsPeriod = $request->input('stats_period', 'today');

        $logs = $this->logService->getLogs($filters, $perPage);
        $stats = $this->logService->getDashboardStats($statsPeriod);
        $filterOptions = $this->logService->getFilterOptions();

        // Transform logs for frontend - paginator will auto-serialize with links
        $logsData = $logs->through(fn ($log) => [
            'id' => $log->id,
            'level' => $log->level,
            'level_color' => $log->severity_color,
            'channel' => $log->channel,
            'message' => $log->message,
            'context' => $log->context,
            'extra' => $log->extra,
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
            ] : null,
            'user_type' => $log->user_type,
            'request' => [
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'method' => $log->request_method,
                'url' => $log->request_url,
                'request_id' => $log->request_id,
            ],
            'performance' => [
                'duration_ms' => $log->duration_ms ? round($log->duration_ms, 2) : null,
                'memory' => $log->formatted_memory,
            ],
            'exception' => $log->exception_class ? [
                'class' => $log->exception_class,
                'message' => $log->exception_message,
                'file' => $log->exception_file,
                'line' => $log->exception_line,
                'trace' => $log->exception_trace,
            ] : null,
            'logged_at' => $log->logged_at?->toIso8601String(),
            'logged_at_human' => $log->logged_at?->diffForHumans(),
        ]);

        return Inertia::render('Admin/Logs', [
            'logs' => $logsData,
            'stats' => $stats,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Display single log detail page
     */
    public function show(int $id): Response
    {
        $log = $this->logService->getLog($id);

        if (!$log) {
            abort(404, 'Log not found');
        }

        // Load user relationship
        $log->load('user');

        // Get related logs from the same request if request_id exists
        $relatedLogs = collect();
        if ($log->request_id) {
            $relatedLogs = $this->logService->getRequestLogs($log->request_id);
        }

        // Transform log data for frontend
        $logData = [
            'id' => $log->id,
            'level' => $log->level,
            'level_color' => $log->severity_color,
            'channel' => $log->channel,
            'message' => $log->message,
            'context' => $log->context,
            'extra' => $log->extra,
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
            ] : null,
            'user_type' => $log->user_type,
            'request' => [
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'method' => $log->request_method,
                'url' => $log->request_url,
                'request_id' => $log->request_id,
            ],
            'performance' => [
                'duration_ms' => $log->duration_ms ? round($log->duration_ms, 2) : null,
                'memory' => $log->formatted_memory,
            ],
            'exception' => $log->exception_class ? [
                'class' => $log->exception_class,
                'message' => $log->exception_message,
                'file' => $log->exception_file,
                'line' => $log->exception_line,
                'trace' => $log->exception_trace,
            ] : null,
            'logged_at' => $log->logged_at?->toIso8601String(),
            'logged_at_human' => $log->logged_at?->diffForHumans(),
        ];

        // Transform related logs
        $relatedLogsData = $relatedLogs->map(fn ($l) => [
            'id' => $l->id,
            'level' => $l->level,
            'message' => $l->message,
            'logged_at' => $l->logged_at?->toIso8601String(),
        ]);

        return Inertia::render('Admin/LogDetail', [
            'log' => $logData,
            'relatedLogs' => $relatedLogsData,
        ]);
    }
}
