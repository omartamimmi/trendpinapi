<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
            'role' => $this['role'],
            'name' => $this['name'],
            'phone' => $this['phone'],
            'email' => $this['email'],
            'profile_image' => $this['profile_image'] ?? null,
            'type' => $this['type'],
            'access_token' => $this['access_token'],
            'expires_at' => $this['expires_at'],
        ];
    }
}
