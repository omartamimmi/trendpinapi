<?php

namespace Modules\Geofence\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'radar_user_id',
        'lat',
        'lng',
        'accuracy',
        'fcm_token',
        'device_id',
        'device_type',
        'metadata',
        'is_tracking_enabled',
        'location_updated_at',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'metadata' => 'array',
        'is_tracking_enabled' => 'boolean',
        'location_updated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for users with tracking enabled
     */
    public function scopeTrackingEnabled($query)
    {
        return $query->where('is_tracking_enabled', true);
    }

    /**
     * Scope for users with valid FCM token
     */
    public function scopeWithFcmToken($query)
    {
        return $query->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');
    }

    /**
     * Update location from Radar event
     */
    public function updateFromRadarEvent(array $eventData): void
    {
        $this->update([
            'lat' => $eventData['location']['coordinates'][1] ?? $this->lat,
            'lng' => $eventData['location']['coordinates'][0] ?? $this->lng,
            'accuracy' => $eventData['location']['accuracy'] ?? $this->accuracy,
            'location_updated_at' => now(),
        ]);
    }
}
