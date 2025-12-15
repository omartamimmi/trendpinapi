<?php

namespace Modules\Business\app\DTO;

use Illuminate\Http\Request;

class BrandFilterDTO
{
    private const MAX_PER_PAGE = 50;
    private const DEFAULT_PER_PAGE = 15;

    public const OFFER_TYPE_BOGO = 'bogo';
    public const OFFER_TYPE_PERCENTAGE = 'percentage';
    public const OFFER_TYPE_FIXED = 'fixed';

    public function __construct(
        public readonly ?string $search = null,
        public readonly ?array $categoryIds = null,
        public readonly ?bool $featured = null,
        public readonly ?string $offerType = null,
        public readonly ?bool $hasActiveOffers = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?int $radius = null,
        public readonly string $sortBy = 'created_at',
        public readonly string $sortOrder = 'desc',
        public readonly int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    /**
     * Create DTO from HTTP Request
     */
    public static function fromRequest(Request $request): self
    {
        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        return new self(
            search: $request->input('search'),
            categoryIds: self::parseCategoryIds($request),
            featured: $request->has('featured') ? $request->boolean('featured') : null,
            offerType: self::validateOfferType($request->input('offer_type')),
            hasActiveOffers: $request->has('has_offers') ? $request->boolean('has_offers') : null,
            latitude: $request->filled('lat') ? (float) $request->input('lat') : null,
            longitude: $request->filled('lng') ? (float) $request->input('lng') : null,
            radius: $request->filled('radius') ? (int) $request->input('radius') : null,
            sortBy: $request->input('sort_by', 'created_at'),
            sortOrder: $request->input('sort_order', 'desc'),
            perPage: $perPage,
        );
    }

    /**
     * Parse category IDs from request (supports comma-separated or array format)
     */
    private static function parseCategoryIds(Request $request): ?array
    {
        $categoryIds = $request->input('category_ids');

        if (empty($categoryIds)) {
            return null;
        }

        // Handle comma-separated string: "1,2,3"
        if (is_string($categoryIds)) {
            $ids = array_map('intval', explode(',', $categoryIds));
            return array_filter($ids, fn($id) => $id > 0);
        }

        // Handle array format: ["1", "2", "3"]
        if (is_array($categoryIds)) {
            $ids = array_map('intval', $categoryIds);
            return array_filter($ids, fn($id) => $id > 0);
        }

        return null;
    }

    /**
     * Validate offer type
     */
    private static function validateOfferType(?string $type): ?string
    {
        $validTypes = [self::OFFER_TYPE_BOGO, self::OFFER_TYPE_PERCENTAGE, self::OFFER_TYPE_FIXED];

        if ($type && in_array($type, $validTypes)) {
            return $type;
        }

        return null;
    }

    /**
     * Check if location filter is provided
     */
    public function hasLocationFilter(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Get radius in kilometers (default 10km)
     */
    public function getRadiusKm(): int
    {
        return $this->radius ?? 10;
    }

    /**
     * Convert DTO to array for repository
     */
    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'category_ids' => $this->categoryIds,
            'featured' => $this->featured,
            'offer_type' => $this->offerType,
            'has_active_offers' => $this->hasActiveOffers,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'sort_by' => $this->sortBy,
            'sort_order' => $this->sortOrder,
        ], fn($value) => $value !== null);
    }
}
