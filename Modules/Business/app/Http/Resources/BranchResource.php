<?php

namespace Modules\Business\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'phone' => $this->phone,
            'is_main' => (bool) $this->is_main,
            'status' => $this->status,
            'brand' => new BrandResource($this->whenLoaded('brand')),
        ];
    }
}
