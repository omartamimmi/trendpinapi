<?php

namespace Modules\Geofence\Repositories\Contracts;

use Modules\Geofence\app\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LocationRepositoryInterface
{
    public function find(int $id): ?Location;

    public function getAll(): Collection;

    public function getAllActive(): Collection;

    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator;

    public function create(array $data): Location;

    public function update(int $id, array $data): Location;

    public function delete(int $id): bool;

    public function getWithBranches(int $id): ?Location;

    public function findNearby(float $lat, float $lng, int $radiusMeters = 500): Collection;
}
