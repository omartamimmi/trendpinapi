<?php

namespace Modules\BankOffer\Services\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferBrand;

interface BankOfferServiceInterface
{
    // Offer management
    public function getOffers(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getOffer(int $id): ?BankOffer;
    public function createOffer(array $data): BankOffer;
    public function updateOffer(int $id, array $data): BankOffer;
    public function deleteOffer(int $id): bool;

    // Offer status
    public function approveOffer(int $id, int $approverId): BankOffer;
    public function rejectOffer(int $id): BankOffer;
    public function updateOfferStatus(int $id, string $status): BankOffer;

    // Brand participation
    public function requestParticipation(int $offerId, int $brandId, array $data): BankOfferBrand;
    public function approveParticipation(int $requestId, int $approverId): BankOfferBrand;
    public function rejectParticipation(int $requestId): BankOfferBrand;

    // Queries
    public function getActiveOffersForBrand(int $brandId): Collection;
    public function getOffersWithBrandProviders(int $brandId): array;
    public function getAvailableOffersForRetailer(): Collection;

    // Redemption
    public function redeemOffer(int $offerId, int $userId, ?int $brandId, ?int $branchId, ?float $amount): array;
}
