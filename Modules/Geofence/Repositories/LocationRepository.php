<?php

namespace Modules\Geofence\Repositories;

use Modules\Geofence\Repositories\Contracts\LocationRepositoryInterface;
use Modules\Geofence\app\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LocationRepository implements LocationRepositoryInterface
{
    public function find(int $id): ?Location
    {
        return Location::find($id);
    }

    public function getAll(): Collection
    {
        return Location::orderBy('name')->get();
    }

    public function getAllActive(): Collection
    {
        return Location::active()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?string $type = null): LengthAwarePaginator
    {
        $query = Location::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->withCount('branches')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): Location
    {
        return Location::create($data);
    }

    public function update(int $id, array $data): Location
    {
        $location = $this->find($id);
        $location->update($data);
        return $location->fresh();
    }

    public function delete(int $id): bool
    {
        return Location::destroy($id) > 0;
    }

    public function getWithBranches(int $id): ?Location
    {
        return Location::with(['branches', 'branches.brand', 'geofence'])->find($id);
    }

    /**
     * Find locations within a certain radius of a point
     * Uses Haversine formula for distance calculation
     */
    public function findNearby(float $lat, float $lng, int $radiusMeters = 500): Collection
    {
        $radiusKm = $radiusMeters / 1000;

        return Location::select('*')
            ->selectRaw("
                (6371 * acos(cos(radians(?))
                * cos(radians(lat))
                * cos(radians(lng) - radians(?))
                + sin(radians(?))
                * sin(radians(lat)))) AS distance_km
            ", [$lat, $lng, $lat])
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->get();
    }
}
