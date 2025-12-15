<?php

namespace Modules\Geofence\Repositories\Contracts;

use Illuminate\Support\Collection;
use Modules\Geofence\app\Models\Geofence;

interface GeofenceRepositoryInterface
{
    public function findById(int $id): ?Geofence;

    public function findByRadarId(string $radarId): ?Geofence;

    public function findByExternalId(string $externalId): ?Geofence;

    public function findByBranchId(int $branchId): ?Geofence;

    public function findByBrandId(int $brandId): Collection;

    public function getAllActive(): Collection;

    public function getNotSynced(): Collection;

    public function create(array $data): Geofence;

    public function update(int $id, array $data): Geofence;

    public function delete(int $id): bool;

    public function markAsSynced(int $id, string $radarGeofenceId): void;

    public function createFromBranch(int $branchId, array $additionalData = []): Geofence;
}
