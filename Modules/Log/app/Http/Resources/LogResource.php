<?php

namespace Modules\Log\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'level_color' => $this->severity_color,
            'channel' => $this->channel,
            'message' => $this->message,
            'context' => $this->context,
            'extra' => $this->extra,

            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'user_type' => $this->user_type,

            'request' => [
                'ip_address' => $this->ip_address,
                'user_agent' => $this->user_agent,
                'method' => $this->request_method,
                'url' => $this->request_url,
                'request_id' => $this->request_id,
            ],

            'performance' => [
                'duration_ms' => $this->duration_ms ? round($this->duration_ms, 2) : null,
                'memory' => $this->formatted_memory,
            ],

            'exception' => $this->exception_class ? [
                'class' => $this->exception_class,
                'message' => $this->exception_message,
                'file' => $this->exception_file,
                'line' => $this->exception_line,
                'trace' => $this->exception_trace,
            ] : null,

            'logged_at' => $this->logged_at?->toIso8601String(),
            'logged_at_human' => $this->logged_at?->diffForHumans(),
        ];
    }
}
