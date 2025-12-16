<?php

namespace Modules\Business\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'brand_id',
        'location_id',
        'name',
        'location',
        'lat',
        'lng',
        'phone',
        'is_main',
        'status',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'is_main' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(\Modules\Geofence\app\Models\Location::class, 'location_id');
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(\Modules\Geofence\app\Models\Geofence::class, 'id', 'branch_id');
    }
}
