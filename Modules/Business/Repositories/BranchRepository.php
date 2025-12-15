<?php

namespace Modules\Business\Repositories;

use Modules\Business\app\Models\Branch;
use Modules\Business\Repositories\Contracts\BranchRepositoryInterface;

class BranchRepository implements BranchRepositoryInterface
{
    protected Branch $model;

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

    public function getByBrandId(int $brandId)
    {
        return $this->model
            ->where('brand_id', $brandId)
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $branch = $this->model->findOrFail($id);
        $branch->update($data);
        return $branch;
    }

    public function delete(int $id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function deleteByBrandId(int $brandId)
    {
        return $this->model
            ->where('brand_id', $brandId)
            ->delete();
    }

    public function bulkCreate(int $brandId, array $branches)
    {
        $created = [];
        foreach ($branches as $branchData) {
            if (!empty($branchData['name'])) {
                $created[] = $this->model->create([
                    'brand_id' => $brandId,
                    'name' => $branchData['name'],
                    'location' => $branchData['location'] ?? null,
                    'lat' => $branchData['lat'] ?? null,
                    'lng' => $branchData['lng'] ?? null,
                ]);
            }
        }
        return $created;
    }
}
