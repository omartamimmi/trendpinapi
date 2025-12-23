<?php

namespace Modules\BankOffer\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\Repositories\Contracts\BankOfferRepositoryInterface;

class BankOfferRepository implements BankOfferRepositoryInterface
{
    public function __construct(
        protected BankOffer $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['bank', 'cardType', 'creator']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('title_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['bank_id'])) {
            $query->where('bank_id', $filters['bank_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['offer_type'])) {
            $query->where('offer_type', $filters['offer_type']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function find(int $id): ?BankOffer
    {
        return $this->model->with([
            'bank',
            'cardType',
            'creator',
            'approver',
            'approvedBrands',
            'pendingBrands'
        ])->find($id);
    }

    public function create(array $data): BankOffer
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): BankOffer
    {
        $offer = $this->model->findOrFail($id);
        $offer->update($data);
        return $offer->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getActiveOffers(array $filters = []): Collection
    {
        $query = $this->model->active()->with(['bank', 'cardType']);

        if (!empty($filters['bank_id'])) {
            $query->where('bank_id', $filters['bank_id']);
        }

        if (!empty($filters['card_type_id'])) {
            $query->where('card_type_id', $filters['card_type_id']);
        }

        if (!empty($filters['offer_type'])) {
            $query->where('offer_type', $filters['offer_type']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getOffersByBank(int $bankId): Collection
    {
        return $this->model->forBank($bankId)
            ->with(['cardType'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getOffersForBrand(int $brandId): Collection
    {
        return $this->model->active()
            ->whereHas('approvedBrands', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            })
            ->with(['bank', 'cardType'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPendingOffers(): Collection
    {
        return $this->model->pending()
            ->with(['bank', 'creator'])
            ->orderBy('created_at')
            ->get();
    }

    public function approve(int $id, int $approverId): BankOffer
    {
        $offer = $this->model->findOrFail($id);
        $offer->update([
            'status' => 'active',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
        return $offer->fresh();
    }

    public function reject(int $id): BankOffer
    {
        $offer = $this->model->findOrFail($id);
        $offer->update([
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);
        return $offer->fresh();
    }

    public function updateStatus(int $id, string $status): BankOffer
    {
        $offer = $this->model->findOrFail($id);
        $offer->update(['status' => $status]);
        return $offer->fresh();
    }
}
