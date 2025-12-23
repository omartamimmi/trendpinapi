<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class NotificationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'tag',
        'title',
        'body',
        'channels',
        'target_type',
        'target_criteria',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'delivery_stats',
        'action_data',
        'image_url',
        'deep_link',
        'created_by',
    ];

    protected $casts = [
        'channels' => 'array',
        'target_criteria' => 'array',
        'delivery_stats' => 'array',
        'action_data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function deliveries()
    {
        return $this->hasMany(NotificationDelivery::class, 'notification_message_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTag($query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
