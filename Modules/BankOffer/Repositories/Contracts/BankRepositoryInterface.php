<?php

namespace Modules\BankOffer\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\Bank;

interface BankRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Bank;

    public function create(array $data): Bank;

    public function update(int $id, array $data): Bank;

    public function delete(int $id): bool;

    public function getActive(): Collection;

    public function getBanksWithOffers(): Collection;
}
