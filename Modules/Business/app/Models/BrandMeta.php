<?php

namespace Modules\Business\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandMeta extends Model
{
    use HasFactory;

    protected $table = 'brand_meta';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'brand_id',
        'enable_open_hours',
        'open_hours',
        'enable_discount',
        'discount_type',
        'discount'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
