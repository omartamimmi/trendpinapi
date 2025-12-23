<?php

namespace Modules\Geofence\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Geofence\Repositories\Contracts\LocationRepositoryInterface;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Modules\Geofence\Services\Contracts\RadarServiceInterface;
use Illuminate\Support\Facades\DB;

class AdminLocationController extends Controller
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private GeofenceRepositoryInterface $geofenceRepository,
        private RadarServiceInterface $radarService
    ) {}

    /**
     * Display the locations list
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $type = $request->get('type');

        $locations = DB::table('locations')
            ->leftJoin('geofences', 'locations.id', '=', 'geofences.location_id')
            ->select(
                'locations.*',
                'geofences.id as geofence_id',
                'geofences.radar_geofence_id',
                'geofences.last_synced_at as synced_at',
                DB::raw('(SELECT COUNT(*) FROM branches WHERE branches.location_id = locations.id) as branches_count')
            );

        if ($search) {
            $locations->where(function ($q) use ($search) {
                $q->where('locations.name', 'like', "%{$search}%")
                    ->orWhere('locations.name_ar', 'like', "%{$search}%")
                    ->orWhere('locations.city', 'like', "%{$search}%")
                    ->orWhere('locations.address', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $locations->where('locations.type', $type);
        }

        $locations = $locations->orderByDesc('locations.created_at')->paginate($perPage);

        return Inertia::render('Admin/Geofence/Locations', [
            'locations' => $locations,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'types' => [
                ['value' => 'mall', 'label' => 'Mall'],
                ['value' => 'shopping_district', 'label' => 'Shopping District'],
                ['value' => 'plaza', 'label' => 'Plaza'],
                ['value' => 'market', 'label' => 'Market'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    /**
     * Get a single location with its branches
     */
    public function show(int $id): JsonResponse
    {
        $location = $this->locationRepository->getWithBranches($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        return response()->json([
            'success' => true,
            'location' => $location,
        ]);
    }

    /**
     * Store a new location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:mall,shopping_district,plaza,market,other',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:50|max:5000',
            'is_active' => 'boolean',
        ]);

        // Transform latitude/longitude to lat/lng
        $data = $validated;
        $data['lat'] = $validated['latitude'];
        $data['lng'] = $validated['longitude'];
        unset($data['latitude'], $data['longitude']);

        $location = $this->locationRepository->create($data);

        \Illuminate\Support\Facades\Log::info('Creating geofence for location', [
            'location_id' => $location->id,
            'location_name' => $location->name,
        ]);

        // Auto-create geofence for this location
        $geofence = $this->geofenceRepository->create([
            'location_id' => $location->id,
            'name' => $location->name,
            'tag' => 'location',
            'lat' => $location->lat,
            'lng' => $location->lng,
            'radius' => $location->radius,
            'is_active' => $location->is_active,
            'type' => 'circle',
        ]);

        \Illuminate\Support\Facades\Log::info('Geofence created, syncing to Radar', [
            'geofence_id' => $geofence->id,
            'geofence_name' => $geofence->name,
            'location_id' => $geofence->location_id,
            'lat' => $geofence->lat,
            'lng' => $geofence->lng,
        ]);

        // Sync to Radar.io
        $radarSynced = false;
        $radarId = $this->radarService->createGeofence($geofence);

        \Illuminate\Support\Facades\Log::info('Radar sync result', [
            'geofence_id' => $geofence->id,
            'radar_id' => $radarId,
        ]);

        if ($radarId) {
            $this->geofenceRepository->update($geofence->id, [
                'radar_geofence_id' => $radarId,
                'last_synced_at' => now(),
            ]);
            $radarSynced = true;
        }

        $message = 'Location created successfully with geofence';
        if (!$radarSynced) {
            $message .= '. Note: Radar.io sync skipped (API key not configured or sync failed)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'location' => $location->fresh(),
            'geofence_id' => $geofence->id,
            'radar_synced' => $radarSynced,
        ]);
    }

    /**
     * Update a location
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:mall,shopping_district,plaza,market,other',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:50|max:5000',
            'is_active' => 'boolean',
        ]);

        // Transform latitude/longitude to lat/lng
        $data = $validated;
        $data['lat'] = $validated['latitude'];
        $data['lng'] = $validated['longitude'];
        unset($data['latitude'], $data['longitude']);

        $location = $this->locationRepository->update($id, $data);

        // Update associated geofence if exists
        $geofence = $this->geofenceRepository->findByLocation($id);
        if ($geofence) {
            $this->geofenceRepository->update($geofence->id, [
                'name' => $location->name,
                'lat' => $location->lat,
                'lng' => $location->lng,
                'radius' => $location->radius,
                'is_active' => $location->is_active,
            ]);

            // Sync to Radar.io
            $this->radarService->updateGeofence($geofence->fresh());
        }

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'location' => $location,
        ]);
    }

    /**
     * Delete a location
     */
    public function destroy(int $id): JsonResponse
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        // Delete associated geofence first
        $geofence = $this->geofenceRepository->findByLocation($id);
        if ($geofence) {
            if ($geofence->radar_geofence_id) {
                $this->radarService->deleteGeofence($geofence->radar_geofence_id);
            }
            $this->geofenceRepository->delete($geofence->id);
        }

        // Delete the location
        $this->locationRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully',
        ]);
    }

    /**
     * Get branches for a location
     */
    public function branches(int $id): JsonResponse
    {
        $branches = DB::table('branches')
            ->leftJoin('brands', 'branches.brand_id', '=', 'brands.id')
            ->where('branches.location_id', $id)
            ->select(
                'branches.id',
                'branches.name',
                'branches.lat',
                'branches.lng',
                'brands.id as brand_id',
                'brands.name as brand_name'
            )
            ->get();

        return response()->json([
            'success' => true,
            'branches' => $branches,
        ]);
    }

    /**
     * Get all branches (for assignment dropdown)
     */
    public function allBranches(): JsonResponse
    {
        $branches = DB::table('branches')
            ->leftJoin('brands', 'branches.brand_id', '=', 'brands.id')
            ->leftJoin('locations', 'branches.location_id', '=', 'locations.id')
            ->where('branches.status', 'publish')
            ->select(
                'branches.id',
                'branches.name',
                'branches.location_id',
                'brands.id as brand_id',
                'brands.name as brand_name',
                'locations.name as location_name'
            )
            ->orderBy('brands.name')
            ->orderBy('branches.name')
            ->get();

        return response()->json([
            'success' => true,
            'branches' => $branches,
        ]);
    }

    /**
     * Assign branches to a location
     */
    public function assignBranches(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'branch_ids' => 'required|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);

        $location = $this->locationRepository->find($id);
        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        // Remove all current branches from this location
        DB::table('branches')
            ->where('location_id', $id)
            ->update(['location_id' => null]);

        // Assign selected branches to this location
        if (!empty($validated['branch_ids'])) {
            DB::table('branches')
                ->whereIn('id', $validated['branch_ids'])
                ->update(['location_id' => $id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Branches assigned successfully',
            'assigned_count' => count($validated['branch_ids']),
        ]);
    }

    /**
     * Sync location's geofence to Radar.io
     */
    public function sync(int $id): JsonResponse
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        $geofence = $this->geofenceRepository->findByLocation($id);

        if (!$geofence) {
            // Create geofence if doesn't exist
            $geofence = $this->geofenceRepository->create([
                'location_id' => $location->id,
                'name' => $location->name,
                'lat' => $location->lat,
                'lng' => $location->lng,
                'radius' => $location->radius,
                'is_active' => $location->is_active,
                'type' => 'circle',
            ]);
        }

        // Sync to Radar.io
        $radarId = $this->radarService->createGeofence($geofence);
        if ($radarId) {
            $this->geofenceRepository->update($geofence->id, [
                'radar_geofence_id' => $radarId,
                'last_synced_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location synced to Radar.io successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to sync to Radar.io. Check API key configuration.',
        ], 400);
    }
}
