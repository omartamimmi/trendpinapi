<?php

namespace Modules\Business\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Media\Helpers\FileHelper;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'logo' => $this->logo_url,
            'featured_image' => $this->image_id ? FileHelper::url($this->image_id, 'full') : null,
            'gallery' => $this->gallery_images,
            'phone_number' => $this->phone_number,
            'distance' => $this->when(isset($this->distance), fn() => round($this->distance, 2)),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_ar' => $cat->name_ar ?? null,
                ]);
            }),
            'branches_count' => $this->whenCounted('branches'),
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
            'offers' => OfferResource::collection($this->whenLoaded('activeOffers')),
            'participating_banks' => $this->whenLoaded('activeBankOfferBrands', function () {
                $banks = $this->activeBankOfferBrands
                    ->map(fn($item) => $item->bankOffer?->bank)
                    ->filter()
                    ->unique('id')
                    ->values();
                return $banks->map(fn($bank) => [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'name_ar' => $bank->name_ar,
                    'logo' => $bank->logo?->url,
                ]);
            }),
        ];
    }
}
