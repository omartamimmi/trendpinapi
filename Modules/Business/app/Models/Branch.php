<?php

namespace Modules\Business\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Business\Database\Factories\BranchFactory;

class Branch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'brand_id',
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

    // protected static function newFactory(): BranchFactory
    // {
    //     // return BranchFactory::new();
    // }

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

}
