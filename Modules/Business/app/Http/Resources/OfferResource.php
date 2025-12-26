<?php

namespace Modules\Business\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'formatted_discount' => $this->getFormattedDiscount(),
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'max_claims' => $this->max_claims,
            'claims_count' => $this->claims_count,
            'terms' => $this->terms,
            'all_branches' => (bool) $this->all_branches,
            'branch_ids' => $this->branch_ids,
            'is_active' => $this->isActive(),
            'brand' => new BrandResource($this->whenLoaded('brand')),
        ];
    }

    /**
     * Get formatted discount display
     */
    private function getFormattedDiscount(): string
    {
        return match ($this->discount_type) {
            'bogo' => 'Buy 1 Get 1',
            'percentage' => "{$this->discount_value}% Off",
            'fixed' => "SAR {$this->discount_value} Off",
            default => '',
        };
    }
}
