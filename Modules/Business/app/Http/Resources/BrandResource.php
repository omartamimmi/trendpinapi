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
            'featured_image' => $this->featured_mobile ? FileHelper::url($this->image_id, 'full') : null,
            'gallery' => $this->gallery_images,
            'phone_number' => $this->phone_number,
            'website_link' => $this->website_link,
            'insta_link' => $this->insta_link,
            'facebook_link' => $this->facebook_link,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'distance' => $this->when(isset($this->distance), fn() => round($this->distance, 2)),
            'status' => $this->status,
            'open_status' => $this->open_status,
            'days' => $this->days,
            'featured' => (bool) $this->featured,
            'is_wishlisted' => $this->isWishList() === '-solid',
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_ar' => $cat->name_ar ?? null,
                ]);
            }),
            'branches_count' => $this->whenCounted('branches'),
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
            'active_offers_count' => $this->whenCounted('activeOffers'),
            'offers' => OfferResource::collection($this->whenLoaded('activeOffers')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
