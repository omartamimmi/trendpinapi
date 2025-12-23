<?php

namespace Modules\Geofence\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\app\Models\Branch;
use Modules\Business\app\Models\Brand;

class NotificationThrottleLog extends Model
{
    public const STATUS_SENT = 'sent';
    public const STATUS_THROTTLED = 'throttled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public const EVENT_ENTRY = 'entry';
    public const EVENT_EXIT = 'exit';
    public const EVENT_DWELL = 'dwell';

    protected $fillable = [
        'user_id',
        'geofence_id',
        'brand_id',
        'branch_id',
        'offer_id',
        'notification_type',
        'event_type',
        'radar_event_id',
        'user_lat',
        'user_lng',
        'status',
        'skip_reason',
        'notification_data',
        'sent_at',
    ];

    protected $casts = [
        'user_lat' => 'decimal:8',
        'user_lng' => 'decimal:8',
        'notification_data' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the geofence
     */
    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }

    /**
     * Get the brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope for sent notifications
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope for throttled notifications
     */
    public function scopeThrottled($query)
    {
        return $query->where('status', self::STATUS_THROTTLED);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for notifications sent after a specific time
     */
    public function scopeSentAfter($query, $datetime)
    {
        return $query->where('sent_at', '>=', $datetime);
    }

    /**
     * Scope for notifications sent within a period
     */
    public function scopeSentWithinHours($query, int $hours)
    {
        return $query->where('sent_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for notifications sent today
     */
    public function scopeSentToday($query)
    {
        return $query->whereDate('sent_at', today());
    }

    /**
     * Scope for notifications sent this week
     */
    public function scopeSentThisWeek($query)
    {
        return $query->where('sent_at', '>=', now()->startOfWeek());
    }

    /**
     * Create a throttled log entry
     */
    public static function createThrottled(array $data, string $reason): self
    {
        return self::create(array_merge($data, [
            'status' => self::STATUS_THROTTLED,
            'skip_reason' => $reason,
            'sent_at' => now(),
        ]));
    }

    /**
     * Create a skipped log entry
     */
    public static function createSkipped(array $data, string $reason): self
    {
        return self::create(array_merge($data, [
            'status' => self::STATUS_SKIPPED,
            'skip_reason' => $reason,
            'sent_at' => now(),
        ]));
    }
}
