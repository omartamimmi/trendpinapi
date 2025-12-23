<?php

namespace Modules\Geofence\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
            'branch_id' => $this->branch_id,
            'offer_id' => $this->offer_id,
            'geofence_id' => $this->geofence_id,
            'event_type' => $this->event_type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radar_event_id' => $this->radar_event_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'brand' => $this->whenLoaded('brand', fn() => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ]),
            'branch' => $this->whenLoaded('branch', fn() => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ]),
            'offer' => $this->whenLoaded('offer', fn() => [
                'id' => $this->offer->id,
                'name' => $this->offer->name,
            ]),
        ];
    }
}
