<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use LamaLama\Wishlist\HasWishlists;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasWishlists, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider_name',
        'provider_id',
        'device_token',
        'phone',
        'birthday',
        'contact_email',
        'image_id',
        'enable_notification',
        'location_id',
        'appleId',
        'fcm_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleNameAttribute(){
        $all = $this->getRoleNames();

        if(count($all)){
            return ucfirst($all[0]);
        }
        return '';
    }

    public function user_interests()
    {
        return $this->belongsToMany(User::class,
            "user_has_interests",
            "user_id",
            "interest_id")->withPivot('interest_id');
    }

    public function user_interest_shop()
    {
        return $this->belongsToMany(Shop::class,
            "user_has_interest_in_shops",
            "user_id",
            "shop_id")->withPivot('shop_id');
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    public function retailerOnboarding()
    {
        return $this->hasOne(\Modules\RetailerOnboarding\app\Models\RetailerOnboarding::class);
    }
}
