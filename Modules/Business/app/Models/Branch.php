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
    protected $fillable = ['brand_id', 'name'];

    // protected static function newFactory(): BranchFactory
    // {
    //     // return BranchFactory::new();
    // }

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

}
