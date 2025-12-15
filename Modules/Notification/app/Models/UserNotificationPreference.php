<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'tag',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function isEnabled($userId, $channel, $tag)
    {
        $preference = static::where('user_id', $userId)
            ->where('channel', $channel)
            ->where('tag', $tag)
            ->first();

        // If no preference set, default to enabled
        return $preference ? $preference->is_enabled : true;
    }

    public static function setPreference($userId, $channel, $tag, $enabled)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'channel' => $channel,
                'tag' => $tag,
            ],
            ['is_enabled' => $enabled]
        );
    }
}
