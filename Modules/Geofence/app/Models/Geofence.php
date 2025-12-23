<?php

namespace Modules\Geofence\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\app\Models\Branch;
use Modules\Business\app\Models\Brand;

class Geofence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'location_id',
        'radar_geofence_id',
        'external_id',
        'tag',
        'branch_id',
        'brand_id',
        'name',
        'description',
        'lat',
        'lng',
        'radius',
        'type',
        'coordinates',
        'metadata',
        'is_active',
        'synced_to_radar',
        'last_synced_at',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'radius' => 'integer',
        'coordinates' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'synced_to_radar' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the location associated with this geofence
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the branch associated with this geofence
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the brand associated with this geofence
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Scope for active geofences
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for geofences not synced to Radar
     */
    public function scopeNotSynced($query)
    {
        return $query->where('synced_to_radar', false);
    }

    /**
     * Scope for geofences synced to Radar
     */
    public function scopeSynced($query)
    {
        return $query->where('synced_to_radar', true);
    }

    /**
     * Get the external ID for Radar.io
     */
    public function getRadarExternalId(): string
    {
        if ($this->location_id) {
            return "location_{$this->location_id}";
        }

        if ($this->branch_id) {
            return "branch_{$this->branch_id}";
        }

        if ($this->brand_id) {
            return "brand_{$this->brand_id}";
        }

        return "geofence_{$this->id}";
    }

    /**
     * Get the tag for Radar.io
     */
    public function getRadarTag(): string
    {
        return $this->tag ?? 'trendpin';
    }

    /**
     * Convert to Radar.io geofence format
     */
    public function toRadarFormat(): array
    {
        return [
            'description' => $this->name,
            'tag' => $this->getRadarTag(),
            'externalId' => $this->getRadarExternalId(),
            'type' => $this->type,
            'coordinates' => $this->type === 'circle'
                ? [$this->lng, $this->lat]
                : $this->coordinates,
            'radius' => $this->radius,
            'enabled' => $this->is_active,
            'metadata' => array_merge($this->metadata ?? [], [
                'geofence_id' => $this->id,
                'location_id' => $this->location_id,
                'branch_id' => $this->branch_id,
                'brand_id' => $this->brand_id,
            ]),
        ];
    }
}
