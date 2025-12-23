<?php

namespace Modules\BankOffer\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Business\app\Http\Resources\BrandResource;

class BankOfferBrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_offer_id' => $this->bank_offer_id,
            'bank_offer' => $this->whenLoaded('bankOffer', function () {
                return new BankOfferResource($this->bankOffer);
            }),
            'brand_id' => $this->brand_id,
            'brand' => $this->whenLoaded('brand', function () {
                return new BrandResource($this->brand);
            }),
            'all_branches' => $this->all_branches,
            'branch_ids' => $this->branch_ids,
            'status' => $this->status,
            'requested_at' => $this->requested_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'approved_by' => $this->approved_by,
            'approver' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
