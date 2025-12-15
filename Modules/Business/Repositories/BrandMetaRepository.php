<?php

namespace Modules\Business\Repositories;

use Modules\Business\app\Models\BrandMeta;
use Modules\Business\Repositories\Contracts\BrandMetaRepositoryInterface;

class BrandMetaRepository implements BrandMetaRepositoryInterface
{
    protected BrandMeta $model;

    public function __construct(BrandMeta $model)
    {
        $this->model = $model;
    }

    public function getMetaBrand(int $brandId)
    {
        return $this->model->where('brand_id', $brandId)->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $brandId)
    {
        return $this->model
            ->where('brand_id', $brandId)
            ->update($data);
    }

    public function delete(int $brandId)
    {
        return $this->model
            ->where('brand_id', $brandId)
            ->delete();
    }
}
