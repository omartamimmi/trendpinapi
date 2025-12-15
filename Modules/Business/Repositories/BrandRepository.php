<?php

namespace Modules\Business\Repositories;

use Modules\Business\app\Models\Brand;
use Modules\Business\Repositories\Contracts\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface
{
    protected Brand $model;

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
}
