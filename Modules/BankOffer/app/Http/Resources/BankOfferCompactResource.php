<?php

namespace Modules\BankOffer\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankOfferCompactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
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
            'bank' => $this->whenLoaded('bank', fn() => [
                'id' => $this->bank->id,
                'name' => $this->bank->name,
                'name_ar' => $this->bank->name_ar,
                'logo' => $this->bank->logo?->url,
            ]),
            'card_type' => $this->whenLoaded('cardType', fn() => $this->cardType ? [
                'id' => $this->cardType->id,
                'name' => $this->cardType->name,
                'name_ar' => $this->cardType->name_ar,
                'card_network' => $this->cardType->card_network,
            ] : null),
        ];
    }
}
