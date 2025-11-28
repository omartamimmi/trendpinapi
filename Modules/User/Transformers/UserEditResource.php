<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['user']->id,
            'name' => $this['user']->name,
            'business_name' => $this['user']->business_name,
            'phone' => $this['user']->phone,
            'role' => $this['user']->roles->pluck('id'),
            'first_name' => $this['user']->first_name,
            'last_name' => $this['user']->last_name,
            'email' => $this['user']->email,
            'address' => $this['user']->address,
            'address2' => $this['user']->address2,
            'city' => $this['user']->city,
            'birthday' => $this['user']->birthday,
            'status' => $this['user']->status,
            'avatar_id' => $this['user']->avatar_id,
            'created_at' => $this['user']->created_at,
            'slug' => $this['user']->slug,
            'state' => $this['user']->state,
            'country' => $this['user']->country,
            'zip_code' => $this['user']->zip_code,
            'time_zone' => $this['user']->time_zone,
            'bio' => $this['user']->bio,
            'vendor_commission_type' => $this['user']->vendor_commission_type,
            'vendor_commission_amount' => $this['user']->vendor_commission_amount,
            'meta' => $this['meta'],
            'verify_submit_status'=> $this['user']->verify_submit_status ?? '',
            'upgradeRequest' => $this['upgradeRequest'],
            'paymentSetting' => $this['paymentSetting'],
            'roles' => $this['roles'],
            'user_translation' => $this['user_translation'],
        ];
    }
}
