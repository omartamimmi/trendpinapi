<?php

namespace Modules\Business\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Business\Database\Factories\GroupFactory;

class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['business_id','name'];

    // protected static function newFactory(): GroupFactory
    // {
    //     // return GroupFactory::new();
    // }

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function brands() {
        return $this->hasMany(Brand::class);
    }

}
