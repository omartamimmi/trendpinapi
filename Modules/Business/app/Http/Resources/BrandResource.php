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
        $bestBy = $request->query('best_by', 'highest_value');

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
            'is_wishlisted' => $this->isWishList() === '-solid',
            'distance' => $this->when(isset($this->distance), fn() => round($this->distance, 2)),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_ar' => $cat->name_ar ?? null,
                ]);
            }),
            'best_offer' => $this->formatBestOffer($this->getBestOffer($bestBy)),
            'best_bank_offer' => $this->formatBestBankOffer($this->getBestBankOffer($bestBy)),
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

    /**
     * Format best brand offer for display
     */
    private function formatBestOffer($offer): ?array
    {
        if (!$offer) {
            return null;
        }

        return [
            'id' => $offer->id,
            'name' => $offer->name,
            'discount_type' => $offer->discount_type,
            'discount_value' => $offer->discount_value,
            'label' => $this->getOfferLabel($offer->discount_type, $offer->discount_value),
            'end_date' => $offer->end_date?->toDateString(),
        ];
    }

    /**
     * Format best bank offer for display
     */
    private function formatBestBankOffer($offer): ?array
    {
        if (!$offer) {
            return null;
        }

        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'title_ar' => $offer->title_ar,
            'offer_type' => $offer->offer_type,
            'offer_value' => $offer->offer_value,
            'label' => $this->getOfferLabel($offer->offer_type, $offer->offer_value),
            'end_date' => $offer->end_date?->toDateString(),
            'bank' => $offer->bank ? [
                'id' => $offer->bank->id,
                'name' => $offer->bank->name,
                'name_ar' => $offer->bank->name_ar,
                'logo' => $offer->bank->logo?->url,
            ] : null,
        ];
    }

    /**
     * Generate a display label for offer
     */
    private function getOfferLabel(string $type, $value): string
    {
        return match ($type) {
            'percentage' => "{$value}% Off",
            'fixed' => "JOD {$value} Off",
            'cashback' => "{$value}% Cashback",
            'bogo' => 'Buy 1 Get 1',
            default => "{$value}% Off",
        };
    }
}
