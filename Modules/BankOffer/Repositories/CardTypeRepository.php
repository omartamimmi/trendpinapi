<?php

namespace Modules\BankOffer\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\CardType;
use Modules\BankOffer\Repositories\Contracts\CardTypeRepositoryInterface;

class CardTypeRepository implements CardTypeRepositoryInterface
{
    public function __construct(
        protected CardType $model
    ) {}

    public function all(): Collection
    {
        return $this->model->with('bank')->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('bank');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['bank_id'])) {
            $query->where('bank_id', $filters['bank_id']);
        }

        if (!empty($filters['card_network'])) {
            $query->where('card_network', $filters['card_network']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function find(int $id): ?CardType
    {
        return $this->model->with(['bank', 'logo'])->find($id);
    }

    public function create(array $data): CardType
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): CardType
    {
        $cardType = $this->model->findOrFail($id);
        $cardType->update($data);
        return $cardType->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getByBank(int $bankId): Collection
    {
        return $this->model->where('bank_id', $bankId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getGenericCards(): Collection
    {
        return $this->model->generic()
            ->active()
            ->orderBy('name')
            ->get();
    }
}
