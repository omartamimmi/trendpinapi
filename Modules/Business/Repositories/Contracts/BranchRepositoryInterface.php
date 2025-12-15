<?php

namespace Modules\Business\Repositories\Contracts;

interface BranchRepositoryInterface
{
    public function getByBrandId(int $brandId);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);

    public function deleteByBrandId(int $brandId);
}
