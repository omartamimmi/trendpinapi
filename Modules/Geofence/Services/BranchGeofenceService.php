<?php

namespace Modules\Geofence\Services;

use Modules\Business\app\Models\Branch;
use Modules\Geofence\app\Models\Geofence;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Modules\Geofence\Services\Contracts\RadarServiceInterface;
use Illuminate\Support\Facades\Log;

class BranchGeofenceService
{
    public function __construct(
        private GeofenceRepositoryInterface $geofenceRepository,
        private RadarServiceInterface $radarService
    ) {}

    /**
     * Handle geofence creation/linking when a branch is created or updated
     *
     * @param Branch $branch The branch model
     * @param int|null $locationId The location (area) ID if branch is inside an area
     * @return array Result with geofence info
     */
    public function handleBranchGeofence(Branch $branch, ?int $locationId = null): array
    {
        $result = [
            'branch_id' => $branch->id,
            'location_id' => $locationId,
            'geofence_created' => false,
            'linked_to_location' => false,
            'geofence_id' => null,
            'radar_synced' => false,
        ];

        // If branch is inside a location (mall, shopping district, etc.)
        if ($locationId) {
            // Update branch to link to this location
            $branch->update(['location_id' => $locationId]);
            $result['linked_to_location'] = true;

            // The branch will use the location's geofence for notifications
            // No need to create a separate geofence
            Log::info('Branch linked to location geofence', [
                'branch_id' => $branch->id,
                'location_id' => $locationId,
            ]);

            return $result;
        }

        // If branch has coordinates and is NOT inside a location, create standalone geofence
        if ($branch->lat && $branch->lng) {
            // Check if branch already has a geofence
            $existingGeofence = Geofence::where('branch_id', $branch->id)->first();

            if ($existingGeofence) {
                // Update existing geofence
                $existingGeofence->update([
                    'name' => $branch->name,
                    'lat' => $branch->lat,
                    'lng' => $branch->lng,
                    'is_active' => $branch->status === 'publish',
                ]);

                // Sync to Radar
                $this->radarService->updateGeofence($existingGeofence);

                $result['geofence_id'] = $existingGeofence->id;
                $result['geofence_updated'] = true;

                return $result;
            }

            // Create new geofence for this branch
            $geofence = $this->geofenceRepository->create([
                'branch_id' => $branch->id,
                'brand_id' => $branch->brand_id,
                'name' => $branch->name,
                'tag' => 'branch',
                'lat' => $branch->lat,
                'lng' => $branch->lng,
                'radius' => 100, // Default 100m radius for branches
                'type' => 'circle',
                'is_active' => $branch->status === 'publish',
            ]);

            $result['geofence_created'] = true;
            $result['geofence_id'] = $geofence->id;

            // Sync to Radar.io
            $radarId = $this->radarService->createGeofence($geofence);
            if ($radarId) {
                $this->geofenceRepository->update($geofence->id, [
                    'radar_geofence_id' => $radarId,
                    'last_synced_at' => now(),
                ]);
                $result['radar_synced'] = true;
            }

            Log::info('Geofence created for branch', [
                'branch_id' => $branch->id,
                'geofence_id' => $geofence->id,
                'radar_synced' => $result['radar_synced'],
            ]);
        }

        return $result;
    }

    /**
     * Remove geofence when branch is deleted
     */
    public function removeBranchGeofence(Branch $branch): void
    {
        $geofence = Geofence::where('branch_id', $branch->id)->first();

        if ($geofence) {
            // Delete from Radar.io
            if ($geofence->radar_geofence_id) {
                $this->radarService->deleteGeofence($geofence->radar_geofence_id);
            }

            // Delete geofence
            $this->geofenceRepository->delete($geofence->id);

            Log::info('Geofence removed for branch', [
                'branch_id' => $branch->id,
                'geofence_id' => $geofence->id,
            ]);
        }
    }

    /**
     * Get all available locations (areas) for selection
     */
    public function getAvailableLocations(): \Illuminate\Support\Collection
    {
        return \Modules\Geofence\app\Models\Location::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'city', 'lat', 'lng', 'radius']);
    }
}
