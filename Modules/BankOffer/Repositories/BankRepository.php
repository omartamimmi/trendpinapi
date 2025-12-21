<?php

namespace Modules\BankOffer\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\Repositories\Contracts\BankRepositoryInterface;

class BankRepository implements BankRepositoryInterface
{
    public function __construct(
        protected Bank $model
    ) {}

    public function all(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function find(int $id): ?Bank
    {
        return $this->model->with(['logo', 'cardTypes'])->find($id);
    }

    public function create(array $data): Bank
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Bank
    {
        $bank = $this->model->findOrFail($id);
        $bank->update($data);
        return $bank->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    public function getBanksWithOffers(): Collection
    {
        return $this->model->active()
            ->whereHas('activeOffers')
            ->with('logo')
            ->orderBy('name')
            ->get();
    }
}
