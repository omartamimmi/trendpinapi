<?php

namespace Modules\Business\Repositories\Contracts;

interface BrandMetaRepositoryInterface
{
    public function getMetaBrand(int $brandId);

    public function create(array $data);

    public function update(array $data, int $brandId);
}
