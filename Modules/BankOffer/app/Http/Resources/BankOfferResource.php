<?php

namespace Modules\BankOffer\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_id' => $this->bank_id,
            'bank' => $this->whenLoaded('bank', function () {
                return new BankResource($this->bank);
            }),
            'card_type_id' => $this->card_type_id,
            'card_type' => $this->whenLoaded('cardType', function () {
                return new CardTypeResource($this->cardType);
            }),
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'offer_type' => $this->offer_type,
            'offer_value' => $this->offer_value,
            'min_purchase_amount' => $this->min_purchase_amount,
            'max_discount_amount' => $this->max_discount_amount,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'terms' => $this->terms,
            'terms_ar' => $this->terms_ar,
            'redemption_type' => $this->redemption_type,
            'status' => $this->status,
            'total_claims' => $this->total_claims,
            'max_claims' => $this->max_claims,
            'is_active' => $this->isActive(),
            'has_reached_limit' => $this->hasReachedLimit(),
            'participating_brands_count' => $this->whenCounted('participatingBrands'),
            'participating_brands' => BankOfferBrandResource::collection($this->whenLoaded('participatingBrands')),
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
