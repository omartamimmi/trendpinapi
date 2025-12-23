<?php

namespace Modules\BankOffer\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\BankOffer\app\Models\BankOfferRedemption;
use Modules\BankOffer\Repositories\Contracts\BankOfferRepositoryInterface;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;
use Modules\Business\app\Models\Brand;
use Exception;

class BankOfferService implements BankOfferServiceInterface
{
    public function __construct(
        protected BankOfferRepositoryInterface $offerRepository
    ) {}

    public function getOffers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->offerRepository->paginate($perPage, $filters);
    }

    public function getOffer(int $id): ?BankOffer
    {
        return $this->offerRepository->find($id);
    }

    public function createOffer(array $data): BankOffer
    {
        return $this->offerRepository->create($data);
    }

    public function updateOffer(int $id, array $data): BankOffer
    {
        return $this->offerRepository->update($id, $data);
    }

    public function deleteOffer(int $id): bool
    {
        return $this->offerRepository->delete($id);
    }

    public function approveOffer(int $id, int $approverId): BankOffer
    {
        return $this->offerRepository->approve($id, $approverId);
    }

    public function rejectOffer(int $id): BankOffer
    {
        return $this->offerRepository->reject($id);
    }

    public function updateOfferStatus(int $id, string $status): BankOffer
    {
        return $this->offerRepository->updateStatus($id, $status);
    }

    public function requestParticipation(int $offerId, int $brandId, array $data): BankOfferBrand
    {
        // Check if offer exists and is active
        $offer = $this->offerRepository->find($offerId);
        if (!$offer || !$offer->isActive()) {
            throw new Exception('Offer not available for participation');
        }

        // Check if already requested
        $existing = BankOfferBrand::where('bank_offer_id', $offerId)
            ->where('brand_id', $brandId)
            ->first();

        if ($existing) {
            throw new Exception('Participation already requested');
        }

        return BankOfferBrand::create([
            'bank_offer_id' => $offerId,
            'brand_id' => $brandId,
            'all_branches' => $data['all_branches'] ?? true,
            'branch_ids' => $data['branch_ids'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }

    public function approveParticipation(int $requestId, int $approverId): BankOfferBrand
    {
        $request = BankOfferBrand::findOrFail($requestId);
        $request->approve($approverId);
        return $request->fresh();
    }

    public function rejectParticipation(int $requestId): BankOfferBrand
    {
        $request = BankOfferBrand::findOrFail($requestId);
        $request->reject();
        return $request->fresh();
    }

    public function getActiveOffersForBrand(int $brandId): Collection
    {
        return $this->offerRepository->getOffersForBrand($brandId);
    }

    public function getOffersWithBrandProviders(int $brandId): array
    {
        // Get brand info
        $brand = Brand::with('logo')->find($brandId);
        if (!$brand) {
            return ['offer_providers' => [], 'offers' => []];
        }

        // Get bank offers for this brand
        $bankOffers = $this->getActiveOffersForBrand($brandId);

        // Get unique banks from these offers
        $banks = $bankOffers->pluck('bank')->unique('id')->values();

        // Build offer providers array
        $offerProviders = collect();

        // Add brand as first provider
        $offerProviders->push([
            'type' => 'brand',
            'id' => $brand->id,
            'name' => $brand->name,
            'logo' => $brand->logo?->url ?? null,
        ]);

        // Add banks as providers
        foreach ($banks as $bank) {
            $offerProviders->push([
                'type' => 'bank',
                'id' => $bank->id,
                'name' => $bank->name,
                'logo' => $bank->logo?->url ?? null,
            ]);
        }

        return [
            'offer_providers' => $offerProviders->toArray(),
            'bank_offers' => $bankOffers,
            'settings' => [
                'offer_mode' => config('bankoffer.offer_mode', 'both'),
                'default_selection' => 'brand',
            ],
        ];
    }

    public function getAvailableOffersForRetailer(): Collection
    {
        return $this->offerRepository->getActiveOffers();
    }

    public function redeemOffer(int $offerId, int $userId, ?int $brandId, ?int $branchId, ?float $amount): array
    {
        return DB::transaction(function () use ($offerId, $userId, $brandId, $branchId, $amount) {
            $offer = $this->offerRepository->find($offerId);

            if (!$offer || !$offer->isActive()) {
                throw new Exception('Offer is not active or has expired');
            }

            if ($offer->hasReachedLimit()) {
                throw new Exception('Offer has reached its claim limit');
            }

            // Calculate discount
            $discountApplied = $amount ? $offer->calculateDiscount($amount) : null;

            // Record redemption
            $redemption = BankOfferRedemption::recordRedemption(
                $offerId,
                $userId,
                $brandId,
                $branchId,
                $amount,
                $discountApplied
            );

            return [
                'redemption' => $redemption,
                'discount_applied' => $discountApplied,
                'original_amount' => $amount,
                'final_amount' => $amount ? ($amount - $discountApplied) : null,
            ];
        });
    }
}
