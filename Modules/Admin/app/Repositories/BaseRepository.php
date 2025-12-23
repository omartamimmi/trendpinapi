<?php

namespace Modules\Admin\app\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Admin\app\Repositories\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function search(string $term, array $columns = []): Collection
    {
        $query = $this->model->newQuery();

        foreach ($columns as $column) {
            $query->orWhere($column, 'like', "%{$term}%");
        }

        return $query->get();
    }

    protected function getPerPage(): int
    {
        return config('admin.pagination.per_page', 20);
    }
}
