<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this['id'],
            'name' => $this['name'],
            'phone' => $this['phone'],
            'email' => $this['email'],
            'role' => $this['role'],
            // 'avatar'=>$this->avatar,
            'access_token' => $this['access_token'],
            'expires_at'=>$this['expires_at'],
            // 'provider_id'=>$this->provider_id,
            // 'created_at' => $this['created_at'],
        ];
    }
}
