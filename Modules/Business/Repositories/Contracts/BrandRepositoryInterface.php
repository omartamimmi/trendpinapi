<?php

namespace Modules\Business\Repositories\Contracts;

interface BrandRepositoryInterface
{
    public function getAllBrandsByAuthor(int $userId);

    public function getBrandById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function deleteBrand(int $id);
}
