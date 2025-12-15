<?php

namespace Modules\Admin\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminActivityLog
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'secret',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->logActivity($request, $response);

        return $response;
    }

    protected function logActivity(Request $request, Response $response): void
    {
        $user = $request->user('admin');

        if (!$user) {
            return;
        }

        $data = [
            'admin_id' => $user->id,
            'admin_email' => $user->email,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'input' => $this->filterSensitiveData($request->except($this->sensitiveFields)),
        ];

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            Log::channel('admin')->info('Admin Activity', $data);
        }
    }

    protected function filterSensitiveData(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}
