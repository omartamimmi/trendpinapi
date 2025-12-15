<?php

namespace Modules\Business\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Business\app\DTO\BrandFilterDTO;
use Modules\Business\app\Models\Brand;
use Modules\Business\Repositories\Contracts\BrandRepositoryInterface;
use Modules\Business\Services\Contracts\BrandApiServiceInterface;

class BrandApiService implements BrandApiServiceInterface
{
    public function __construct(
        private readonly BrandRepositoryInterface $brandRepository
    ) {}

    /**
     * Get paginated list of published brands
     */
    public function getPublishedBrands(BrandFilterDTO $filters): LengthAwarePaginator
    {
        return $this->brandRepository->getPaginatedPublishedBrands(
            $filters->toArray(),
            $filters->perPage
        );
    }

    /**
     * Get a single published brand by ID with branches
     */
    public function getPublishedBrandById(int $id): ?Brand
    {
        return $this->brandRepository->getPublishedBrandWithBranches($id);
    }

    /**
     * Get a single published brand by slug with branches
     */
    public function getPublishedBrandBySlug(string $slug): ?Brand
    {
        return $this->brandRepository->getPublishedBrandBySlugWithBranches($slug);
    }
}
