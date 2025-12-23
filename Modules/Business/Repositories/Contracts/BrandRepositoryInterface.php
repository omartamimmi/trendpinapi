<?php

namespace Modules\Business\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Business\app\Models\Brand;

interface BrandRepositoryInterface
{
    public function getAllBrandsByAuthor(int $userId);

    public function getBrandById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function deleteBrand(int $id);

    /**
     * Get paginated published brands with optional filters
     */
    public function getPaginatedPublishedBrands(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a published brand by ID with its published branches
     */
    public function getPublishedBrandWithBranches(int $id): ?Brand;

    /**
     * Get a published brand by slug with its published branches
     */
    public function getPublishedBrandBySlugWithBranches(string $slug): ?Brand;
}
