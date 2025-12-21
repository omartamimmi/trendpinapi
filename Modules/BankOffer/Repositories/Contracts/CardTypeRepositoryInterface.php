<?php

namespace Modules\BankOffer\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\CardType;

interface CardTypeRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?CardType;

    public function create(array $data): CardType;

    public function update(int $id, array $data): CardType;

    public function delete(int $id): bool;

    public function getByBank(int $bankId): Collection;

    public function getGenericCards(): Collection;
}
