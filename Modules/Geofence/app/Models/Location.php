<?php

namespace Modules\Geofence\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Location extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'type',
        'address',
        'address_ar',
        'city',
        'lat',
        'lng',
        'radius',
        'is_active',
        'image',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branches at this location.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(\App\Models\Branch::class);
    }

    /**
     * Get the geofence for this location.
     */
    public function geofence(): HasOne
    {
        return $this->hasOne(Geofence::class);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'mall' => 'Mall',
            'shopping_district' => 'Shopping District',
            'plaza' => 'Plaza',
            'market' => 'Market',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    /**
     * Scope to get active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
