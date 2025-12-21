<?php

namespace Modules\BankOffer\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'logo' => $this->logo ? [
                'id' => $this->logo->id,
                'url' => $this->logo->url,
            ] : null,
            'description' => $this->description,
            'status' => $this->status,
            'offers_count' => $this->whenCounted('offers'),
            'card_types_count' => $this->whenCounted('cardTypes'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
