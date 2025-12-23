<?php

namespace Modules\Log\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
        'extra',
        'user_id',
        'user_type',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'request_id',
        'duration_ms',
        'memory_usage',
        'exception_class',
        'exception_message',
        'exception_trace',
        'exception_file',
        'exception_line',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'logged_at' => 'datetime',
        'duration_ms' => 'float',
        'memory_usage' => 'integer',
        'exception_line' => 'integer',
    ];

    /**
     * Log levels in order of severity
     */
    public const LEVELS = [
        'debug' => 100,
        'info' => 200,
        'notice' => 250,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
        'alert' => 550,
        'emergency' => 600,
    ];

    /**
     * Common channels
     */
    public const CHANNELS = [
        'application',
        'auth',
        'api',
        'queue',
        'database',
        'security',
        'payment',
        'notification',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for efficient querying

    public function scopeLevel(Builder $query, string|array $level): Builder
    {
        if (is_array($level)) {
            return $query->whereIn('level', $level);
        }
        return $query->where('level', $level);
    }

    public function scopeChannel(Builder $query, string|array $channel): Builder
    {
        if (is_array($channel)) {
            return $query->whereIn('channel', $channel);
        }
        return $query->where('channel', $channel);
    }

    public function scopeFromUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFromUserType(Builder $query, string $userType): Builder
    {
        return $query->where('user_type', $userType);
    }

    public function scopeFromIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeDateRange(Builder $query, ?string $from = null, ?string $to = null): Builder
    {
        if ($from) {
            $query->where('logged_at', '>=', $from);
        }
        if ($to) {
            $query->where('logged_at', '<=', $to);
        }
        return $query;
    }

    public function scopeWithExceptions(Builder $query): Builder
    {
        return $query->whereNotNull('exception_class');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('message', 'like', "%{$term}%")
              ->orWhere('exception_message', 'like', "%{$term}%")
              ->orWhere('request_url', 'like', "%{$term}%");
        });
    }

    public function scopeRequestId(Builder $query, string $requestId): Builder
    {
        return $query->where('request_id', $requestId);
    }

    public function scopeMinLevel(Builder $query, string $minLevel): Builder
    {
        $minSeverity = self::LEVELS[$minLevel] ?? 0;
        $levelsToInclude = array_keys(array_filter(
            self::LEVELS,
            fn($severity) => $severity >= $minSeverity
        ));

        return $query->whereIn('level', $levelsToInclude);
    }

    /**
     * Get severity color for frontend display
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->level) {
            'emergency', 'alert', 'critical' => 'red',
            'error' => 'orange',
            'warning' => 'yellow',
            'notice' => 'blue',
            'info' => 'green',
            'debug' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if this is an error-level log
     */
    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical', 'alert', 'emergency']);
    }

    /**
     * Get formatted memory usage
     */
    public function getFormattedMemoryAttribute(): ?string
    {
        if (!$this->memory_usage) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->memory_usage;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
