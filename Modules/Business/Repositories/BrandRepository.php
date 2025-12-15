<?php

namespace Modules\Business\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Business\app\Models\Brand;
use Modules\Business\Repositories\Contracts\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface
{
    protected Brand $model;

    private const STATUS_PUBLISH = 'publish';
    private const STATUS_ACTIVE = 'active';
    private const ALLOWED_SORT_FIELDS = ['created_at', 'name', 'title', 'featured', 'distance'];
    private const EARTH_RADIUS_KM = 6371;

    public function __construct(Brand $model)
    {
        $this->model = $model;
    }

    public function getAllBrandsByAuthor(int $userId)
    {
        return $this->model
            ->where('create_user', $userId)
            ->with(['branches', 'categories', 'tags', 'meta'])
            ->get();
    }

    public function getBrandById(int $id)
    {
        return $this->model
            ->with(['branches', 'categories', 'tags', 'meta'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $brand = $this->model->findOrFail($id);
        $brand->update($data);
        return $brand;
    }

    public function deleteBrand(int $id)
    {
        $brand = $this->model->findOrFail($id);
        $brand->branches()->delete();
        return $brand->delete();
    }

    public function getBrandsByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->with(['branches'])
            ->get();
    }

    public function searchBrands(string $query, int $limit = 20)
    {
        return $this->model
            ->where('title', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated published brands with optional filters
     */
    public function getPaginatedPublishedBrands(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->where('status', self::STATUS_PUBLISH)
            ->withCount(['branches', 'activeOffers'])
            ->with(['categories']);

        $this->applySearchFilter($query, $filters);
        $this->applyCategoryFilter($query, $filters);
        $this->applyFeaturedFilter($query, $filters);
        $this->applyOfferTypeFilter($query, $filters);
        $this->applyHasActiveOffersFilter($query, $filters);
        $this->applyLocationFilter($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get a published brand by ID with its published branches
     */
    public function getPublishedBrandWithBranches(int $id): ?Brand
    {
        return $this->model->newQuery()
            ->with([
                'branches' => fn($q) => $q->where('status', self::STATUS_PUBLISH)
                    ->orderBy('is_main', 'desc')
                    ->orderBy('name'),
                'categories',
                'tags',
                'activeOffers'
            ])
            ->where('status', self::STATUS_PUBLISH)
            ->find($id);
    }

    /**
     * Get a published brand by slug with its published branches
     */
    public function getPublishedBrandBySlugWithBranches(string $slug): ?Brand
    {
        return $this->model->newQuery()
            ->with([
                'branches' => fn($q) => $q->where('status', self::STATUS_PUBLISH)
                    ->orderBy('is_main', 'desc')
                    ->orderBy('name'),
                'categories',
                'tags',
                'activeOffers'
            ])
            ->where('status', self::STATUS_PUBLISH)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Apply search filter to query
     */
    private function applySearchFilter(Builder $query, array $filters): void
    {
        if (empty($filters['search'])) {
            return;
        }

        $search = $filters['search'];
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('title_ar', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('description_ar', 'like', "%{$search}%");
        });
    }

    /**
     * Apply category filter to query (supports multiple category IDs)
     */
    private function applyCategoryFilter(Builder $query, array $filters): void
    {
        if (empty($filters['category_ids'])) {
            return;
        }

        $categoryIds = $filters['category_ids'];

        $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }

    /**
     * Apply featured filter to query
     */
    private function applyFeaturedFilter(Builder $query, array $filters): void
    {
        if (!isset($filters['featured'])) {
            return;
        }

        $query->where('featured', (bool) $filters['featured']);
    }

    /**
     * Apply offer type filter to query (e.g., bogo, percentage, fixed)
     */
    private function applyOfferTypeFilter(Builder $query, array $filters): void
    {
        if (empty($filters['offer_type'])) {
            return;
        }

        $offerType = $filters['offer_type'];
        $query->whereHas('activeOffers', function ($q) use ($offerType) {
            $q->where('discount_type', $offerType);
        });
    }

    /**
     * Apply has active offers filter to query
     */
    private function applyHasActiveOffersFilter(Builder $query, array $filters): void
    {
        if (!isset($filters['has_active_offers'])) {
            return;
        }

        if ($filters['has_active_offers']) {
            $query->has('activeOffers');
        } else {
            $query->doesntHave('activeOffers');
        }
    }

    /**
     * Apply location-based filter using Haversine formula
     */
    private function applyLocationFilter(Builder $query, array $filters): void
    {
        if (empty($filters['latitude']) || empty($filters['longitude'])) {
            return;
        }

        $lat = $filters['latitude'];
        $lng = $filters['longitude'];
        $radius = $filters['radius'] ?? 10; // Default 10km

        // Add distance calculation using Haversine formula
        $query->selectRaw("
            *, (
                " . self::EARTH_RADIUS_KM . " * acos(
                    cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->havingRaw('distance <= ?', [$radius]);
    }

    /**
     * Apply sorting to query
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // Special handling for distance sorting (only when location filter is applied)
        if ($sortBy === 'distance' && !empty($filters['latitude']) && !empty($filters['longitude'])) {
            $query->orderBy('distance', $sortOrder);
            return;
        }

        if (in_array($sortBy, self::ALLOWED_SORT_FIELDS)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
