<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_message_id',
        'user_id',
        'channel',
        'provider_id',
        'status',
        'provider_response',
        'provider_message_id',
        'failed_reason',
        'sent_at',
        'delivered_at',
        'read_at',
        'clicked_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function notificationMessage()
    {
        return $this->belongsTo(NotificationMessage::class, 'notification_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(NotificationProvider::class, 'provider_id');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now(), 'status' => 'read']);
    }

    public function markAsClicked()
    {
        $this->update(['clicked_at' => now(), 'status' => 'clicked']);
    }
}
