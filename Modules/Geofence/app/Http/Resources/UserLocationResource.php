<?php

namespace Modules\Geofence\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
            'last_updated_at' => $this->last_updated_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
