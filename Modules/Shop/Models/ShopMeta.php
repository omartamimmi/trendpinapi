<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use LamaLama\Wishlist\Wishlistable;
use Modules\Media\Helpers\FileHelper;

class ShopMeta extends Model
{

    protected $table = "shop_meta";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enable_open_hours',
        'open_hours',
        'enable_discount',
        'discount_type',
        'discount',
        'shop_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'open_hours'       => 'array',
        // 'discount'        => 'array',
    ];

    public function fill(array $attributes)
    {
        if(!empty($attributes)){
            foreach ( $this->fillable as $item ){
                $attributes[$item] = $attributes[$item] ?? null;
            }
        }
        return parent::fill($attributes);
    }
}
