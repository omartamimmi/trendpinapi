<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'notification_settings';

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'category',
        'is_enabled',
        'recipients',
        'channels',
        'templates',
        'placeholders',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'recipients' => 'array',
        'channels' => 'array',
        'templates' => 'array',
        'placeholders' => 'array',
    ];

    /**
     * Scope: Enabled settings
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if a specific channel is enabled for this event
     */
    public function isChannelEnabled(string $channel): bool
    {
        return $this->is_enabled && ($this->channels[$channel] ?? false);
    }

    /**
     * Check if a recipient type should receive this notification
     */
    public function hasRecipient(string $recipientType): bool
    {
        return in_array($recipientType, $this->recipients ?? []);
    }

    /**
     * Get template for a specific recipient and channel
     */
    public function getTemplate(string $recipientType, string $channel): ?array
    {
        return $this->templates[$recipientType][$channel] ?? null;
    }
}
