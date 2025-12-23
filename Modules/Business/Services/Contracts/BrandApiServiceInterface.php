<?php

namespace Modules\Business\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Business\app\DTO\BrandFilterDTO;
use Modules\Business\app\Models\Brand;

interface BrandApiServiceInterface
{
    /**
     * Get paginated list of published brands
     */
    public function getPublishedBrands(BrandFilterDTO $filters): LengthAwarePaginator;

    /**
     * Get a single published brand by ID with branches
     */
    public function getPublishedBrandById(int $id): ?Brand;

    /**
     * Get a single published brand by slug with branches
     */
    public function getPublishedBrandBySlug(string $slug): ?Brand;
}
