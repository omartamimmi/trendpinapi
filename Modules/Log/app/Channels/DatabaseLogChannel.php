<?php

namespace Modules\Log\app\Channels;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class DatabaseLogChannel extends AbstractProcessingHandler
{
    protected string $channel;
    protected array $config;

    public function __construct(array $config = [], Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->config = $config;
        $this->channel = $config['channel'] ?? 'application';
    }

    protected function write(LogRecord $record): void
    {
        // Skip logging if the table doesn't exist yet (during migrations)
        if (!$this->tableExists()) {
            return;
        }

        $data = $this->formatRecord($record);

        try {
            DB::table('activity_logs')->insert($data);
        } catch (\Exception $e) {
            // Silently fail to prevent infinite loops
            // Log to file as fallback if configured
            if (config('logging.channels.database.fallback_to_file', false)) {
                error_log("[{$data['level']}] {$data['message']}");
            }
        }
    }

    protected function formatRecord(LogRecord $record): array
    {
        $context = $record->context;
        $extra = $record->extra;

        $data = [
            'level' => strtolower($record->level->name),
            'channel' => $record->channel ?: $this->channel,
            'message' => $record->message,
            'context' => json_encode($this->sanitizeContext($context)),
            'extra' => json_encode($extra),
            'logged_at' => $record->datetime->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add request context
        try {
            $request = Request::instance();
            if ($request) {
                $data['ip_address'] = $request->ip();
                $data['user_agent'] = substr($request->userAgent() ?? '', 0, 255);
                $data['request_method'] = $request->method();
                $data['request_url'] = substr($request->fullUrl(), 0, 500);
                $data['request_id'] = $request->header('X-Request-ID') ?? ($context['request_id'] ?? null);
            }
        } catch (\Exception $e) {
            // Request might not be available in console commands
        }

        // Add user context
        try {
            $user = Auth::user();
            if ($user) {
                $data['user_id'] = $user->id;
                $data['user_type'] = $this->determineUserType($user);
            }
        } catch (\Exception $e) {
            // Auth might not be available
        }

        // Extract exception data
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
            $data['exception_class'] = get_class($exception);
            $data['exception_message'] = substr($exception->getMessage(), 0, 65535);
            $data['exception_trace'] = substr($exception->getTraceAsString(), 0, 65535);
            $data['exception_file'] = $exception->getFile();
            $data['exception_line'] = $exception->getLine();
        }

        // Add performance metrics
        if (defined('LARAVEL_START')) {
            $data['duration_ms'] = (microtime(true) - LARAVEL_START) * 1000;
        }
        $data['memory_usage'] = memory_get_usage(true);

        return $data;
    }

    protected function sanitizeContext(array $context): array
    {
        // Remove exception object as it can't be JSON serialized
        unset($context['exception']);

        // Remove sensitive data
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        foreach ($sensitiveKeys as $key) {
            if (isset($context[$key])) {
                $context[$key] = '[REDACTED]';
            }
        }

        return $context;
    }

    protected function determineUserType($user): string
    {
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return 'admin';
            }
            if ($user->hasRole('retailer')) {
                return 'retailer';
            }
        }
        return 'customer';
    }

    protected function tableExists(): bool
    {
        static $exists = null;

        if ($exists === null) {
            try {
                $exists = DB::getSchemaBuilder()->hasTable('activity_logs');
            } catch (\Exception $e) {
                $exists = false;
            }
        }

        return $exists;
    }
}
