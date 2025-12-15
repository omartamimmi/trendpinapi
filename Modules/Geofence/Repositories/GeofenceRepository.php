<?php

namespace Modules\Geofence\Repositories;

use Illuminate\Support\Collection;
use Modules\Geofence\app\Models\Geofence;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Modules\Business\app\Models\Branch;

class GeofenceRepository implements GeofenceRepositoryInterface
{
    public function __construct(
        protected Geofence $model
    ) {}

    public function findById(int $id): ?Geofence
    {
        return $this->model->find($id);
    }

    public function findByRadarId(string $radarId): ?Geofence
    {
        return $this->model->where('radar_geofence_id', $radarId)->first();
    }

    public function findByExternalId(string $externalId): ?Geofence
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    public function findByBranchId(int $branchId): ?Geofence
    {
        return $this->model->where('branch_id', $branchId)->first();
    }

    public function findByBrandId(int $brandId): Collection
    {
        return $this->model->where('brand_id', $brandId)->get();
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getNotSynced(): Collection
    {
        return $this->model->active()->notSynced()->get();
    }

    public function create(array $data): Geofence
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Geofence
    {
        $geofence = $this->findById($id);
        $geofence->update($data);
        return $geofence->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function markAsSynced(int $id, string $radarGeofenceId): void
    {
        $this->model->where('id', $id)->update([
            'radar_geofence_id' => $radarGeofenceId,
            'synced_to_radar' => true,
            'last_synced_at' => now(),
        ]);
    }

    public function createFromBranch(int $branchId, array $additionalData = []): Geofence
    {
        $branch = Branch::with('brand')->findOrFail($branchId);

        $data = array_merge([
            'branch_id' => $branch->id,
            'brand_id' => $branch->brand_id,
            'name' => $branch->name ?? $branch->brand->name ?? "Branch {$branch->id}",
            'lat' => $branch->lat,
            'lng' => $branch->lng,
            'radius' => config('geofence.geofence.default_radius', 100),
            'type' => 'circle',
            'external_id' => "branch_{$branch->id}",
            'tag' => 'trendpin',
            'is_active' => $branch->status === 'publish',
        ], $additionalData);

        return $this->create($data);
    }
}
