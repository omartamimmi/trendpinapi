<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'title_template',
        'body_template',
        'action_type',
        'action_data',
        'image_url',
        'deep_link_template',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'action_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(NotificationMessage::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTag($query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function render($data = [])
    {
        $title = $this->title_template;
        $body = $this->body_template;
        $deepLink = $this->deep_link_template;

        foreach ($data as $key => $value) {
            $title = str_replace("{{{$key}}}", $value, $title);
            $body = str_replace("{{{$key}}}", $value, $body);
            if ($deepLink) {
                $deepLink = str_replace("{{{$key}}}", $value, $deepLink);
            }
        }

        return [
            'title' => $title,
            'body' => $body,
            'deep_link' => $deepLink,
        ];
    }
}
