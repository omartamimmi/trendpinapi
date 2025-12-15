<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'provider',
        'name',
        'credentials',
        'is_active',
        'priority',
        'settings',
        'last_tested_at',
        'last_test_result',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    public function deliveries()
    {
        return $this->hasMany(NotificationDelivery::class, 'provider_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePrimary($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}
