<?php

namespace Modules\BankOffer\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\BankOffer;

interface BankOfferRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?BankOffer;

    public function create(array $data): BankOffer;

    public function update(int $id, array $data): BankOffer;

    public function delete(int $id): bool;

    public function getActiveOffers(array $filters = []): Collection;

    public function getOffersByBank(int $bankId): Collection;

    public function getOffersForBrand(int $brandId): Collection;

    public function getPendingOffers(): Collection;

    public function approve(int $id, int $approverId): BankOffer;

    public function reject(int $id): BankOffer;

    public function updateStatus(int $id, string $status): BankOffer;
}
