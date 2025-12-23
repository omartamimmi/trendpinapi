<?php

namespace Modules\BankOffer\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_id' => $this->bank_id,
            'bank' => $this->whenLoaded('bank', function () {
                return [
                    'id' => $this->bank->id,
                    'name' => $this->bank->name,
                ];
            }),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'logo' => $this->logo ? [
                'id' => $this->logo->id,
                'url' => $this->logo->url,
            ] : null,
            'card_network' => $this->card_network,
            'bin_prefixes' => $this->bin_prefixes ?? [],
            'card_color' => $this->card_color,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
