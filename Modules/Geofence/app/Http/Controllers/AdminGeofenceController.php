<?php

namespace Modules\Geofence\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Modules\Geofence\Repositories\Contracts\ThrottleLogRepositoryInterface;
use Modules\Geofence\Repositories\Contracts\UserLocationRepositoryInterface;
use Modules\Geofence\Services\Contracts\RadarServiceInterface;
use Modules\Geofence\Services\Contracts\ThrottleServiceInterface;
use Modules\Geofence\Services\Contracts\GeofenceNotificationServiceInterface;
use Modules\Geofence\app\Http\Resources\GeofenceResource;
use Modules\Geofence\app\Http\Resources\NotificationLogResource;
use Modules\Geofence\app\Http\Resources\UserLocationResource;
use Modules\Geofence\DTO\RadarEventDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminGeofenceController extends Controller
{
    public function __construct(
        private GeofenceRepositoryInterface $geofenceRepository,
        private ThrottleLogRepositoryInterface $throttleLogRepository,
        private UserLocationRepositoryInterface $userLocationRepository,
        private RadarServiceInterface $radarService,
        private ThrottleServiceInterface $throttleService,
        private GeofenceNotificationServiceInterface $notificationService
    ) {}

    /**
     * Display the geofence dashboard
     */
    public function index(): Response
    {
        $stats = $this->getDashboardStats();
        $recentNotifications = $this->throttleLogRepository->getRecentLogs(10);
        $geofences = $this->geofenceRepository->getAllActive();

        return Inertia::render('Admin/Geofence/Dashboard', [
            'stats' => $stats,
            'recentNotifications' => NotificationLogResource::collection($recentNotifications),
            'geofences' => GeofenceResource::collection($geofences),
        ]);
    }

    /**
     * Display the geofence list
     */
    public function geofences(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');

        $query = DB::table('geofences')
            ->leftJoin('brands', 'geofences.brand_id', '=', 'brands.id')
            ->leftJoin('branches', 'geofences.branch_id', '=', 'branches.id')
            ->select(
                'geofences.id',
                'geofences.name',
                'geofences.brand_id',
                'geofences.branch_id',
                'geofences.lat as latitude',
                'geofences.lng as longitude',
                'geofences.radius',
                'geofences.is_active',
                'geofences.radar_geofence_id',
                'geofences.last_synced_at as synced_at',
                'geofences.created_at',
                'brands.name as brand_name',
                'branches.name as branch_name'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('geofences.name', 'like', "%{$search}%")
                    ->orWhere('brands.name', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('geofences.is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('geofences.is_active', false);
        }

        $geofences = $query->orderByDesc('geofences.created_at')->paginate($perPage);

        return Inertia::render('Admin/Geofence/Geofences', [
            'geofences' => $geofences,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Display notification logs
     */
    public function notifications(Request $request): Response
    {
        $perPage = $request->get('per_page', 20);
        $userId = $request->get('user_id');
        $brandId = $request->get('brand_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = DB::table('notification_throttle_logs')
            ->leftJoin('users', 'notification_throttle_logs.user_id', '=', 'users.id')
            ->leftJoin('brands', 'notification_throttle_logs.brand_id', '=', 'brands.id')
            ->leftJoin('branches', 'notification_throttle_logs.branch_id', '=', 'branches.id')
            ->leftJoin('offers', 'notification_throttle_logs.offer_id', '=', 'offers.id')
            ->select(
                'notification_throttle_logs.*',
                'users.name as user_name',
                'users.email as user_email',
                'brands.name as brand_name',
                'branches.name as branch_name',
                'offers.name as offer_title'
            );

        if ($userId) {
            $query->where('notification_throttle_logs.user_id', $userId);
        }

        if ($brandId) {
            $query->where('notification_throttle_logs.brand_id', $brandId);
        }

        if ($dateFrom) {
            $query->whereDate('notification_throttle_logs.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('notification_throttle_logs.created_at', '<=', $dateTo);
        }

        $notifications = $query->orderByDesc('notification_throttle_logs.created_at')->paginate($perPage);

        // Get brands for filter dropdown
        $brands = DB::table('brands')->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Geofence/NotificationLogs', [
            'notifications' => $notifications,
            'brands' => $brands,
            'filters' => [
                'user_id' => $userId,
                'brand_id' => $brandId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Display settings page
     */
    public function settings(): Response
    {
        $config = $this->getConfigFromDatabase();

        return Inertia::render('Admin/Geofence/Settings', [
            'config' => $config,
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'radar_secret_key' => 'nullable|string',
            'radar_publishable_key' => 'nullable|string',
            'radar_webhook_secret' => 'nullable|string',
            'max_per_day' => 'required|integer|min:1|max:50',
            'max_per_week' => 'required|integer|min:1|max:200',
            'min_interval_minutes' => 'required|integer|min:1|max:1440',
            'brand_cooldown_hours' => 'required|integer|min:1|max:168',
            'location_cooldown_hours' => 'required|integer|min:1|max:168',
            'offer_cooldown_hours' => 'required|integer|min:1|max:168',
            'quiet_hours_enabled' => 'required|boolean',
            'quiet_hours_start' => 'required|string',
            'quiet_hours_end' => 'required|string',
        ]);

        // Store settings in database
        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => "geofence.{$key}"],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string)$value, 'updated_at' => now()]
            );
        }

        // Clear config cache
        Cache::forget('geofence_settings');

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Sync geofences to Radar.io
     */
    public function sync(): JsonResponse
    {
        $results = $this->radarService->syncAllGeofences();

        return response()->json([
            'success' => true,
            'message' => 'Geofence sync completed',
            'results' => $results,
        ]);
    }

    /**
     * Create a new geofence
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'branch_id' => 'nullable|exists:branches,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:50|max:5000',
            'is_active' => 'boolean',
        ]);

        // Transform latitude/longitude to lat/lng for database
        $data = $validated;
        $data['lat'] = $validated['latitude'];
        $data['lng'] = $validated['longitude'];
        unset($data['latitude'], $data['longitude']);

        $geofence = $this->geofenceRepository->create($data);

        // Sync to Radar.io
        $radarId = $this->radarService->createGeofence($geofence);
        if ($radarId) {
            $this->geofenceRepository->update($geofence->id, [
                'radar_geofence_id' => $radarId,
                'synced_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Geofence created successfully',
            'geofence' => new GeofenceResource($geofence->fresh()),
        ]);
    }

    /**
     * Update a geofence
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'branch_id' => 'nullable|exists:branches,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:50|max:5000',
            'is_active' => 'boolean',
        ]);

        // Transform latitude/longitude to lat/lng for database
        $data = $validated;
        $data['lat'] = $validated['latitude'];
        $data['lng'] = $validated['longitude'];
        unset($data['latitude'], $data['longitude']);

        $geofence = $this->geofenceRepository->update($id, $data);

        // Sync to Radar.io
        $this->radarService->updateGeofence($geofence);

        return response()->json([
            'success' => true,
            'message' => 'Geofence updated successfully',
            'geofence' => new GeofenceResource($geofence->fresh()),
        ]);
    }

    /**
     * Delete a geofence
     */
    public function destroy(int $id): JsonResponse
    {
        $geofence = $this->geofenceRepository->find($id);

        if ($geofence && $geofence->radar_geofence_id) {
            $this->radarService->deleteGeofence($geofence->radar_geofence_id);
        }

        $this->geofenceRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Geofence deleted successfully',
        ]);
    }

    /**
     * Display the test/simulation page
     */
    public function test(): Response
    {
        // Get all users for testing
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Get all active geofences
        $geofences = DB::table('geofences')
            ->leftJoin('brands', 'geofences.brand_id', '=', 'brands.id')
            ->leftJoin('branches', 'geofences.branch_id', '=', 'branches.id')
            ->select(
                'geofences.id',
                'geofences.name',
                'geofences.lat as latitude',
                'geofences.lng as longitude',
                'geofences.radius',
                'geofences.brand_id',
                'geofences.branch_id',
                'brands.name as brand_name',
                'branches.name as branch_name'
            )
            ->where('geofences.is_active', true)
            ->orderBy('geofences.name')
            ->get();

        // Get all brands with active offers
        $brands = DB::table('brands')
            ->select('id', 'name')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('offers')
                    ->whereColumn('offers.brand_id', 'brands.id')
                    ->where('offers.status', 'active');
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Geofence/Test', [
            'users' => $users,
            'geofences' => $geofences,
            'brands' => $brands,
        ]);
    }

    /**
     * Simulate a geofence entry event
     */
    public function simulateEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'geofence_id' => 'nullable|exists:geofences,id',
            'brand_id' => 'nullable|exists:brands,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'event_type' => 'required|in:entry,exit,dwell',
            'skip_throttle' => 'boolean',
        ]);

        // Build metadata
        $metadata = [
            'user_id' => $validated['user_id'],
            'simulated' => true,
        ];

        $branchId = null;
        $brandId = $validated['brand_id'] ?? null;

        // Get geofence details if provided
        if (!empty($validated['geofence_id'])) {
            $geofence = $this->geofenceRepository->find($validated['geofence_id']);
            if ($geofence) {
                $metadata['geofence_id'] = $geofence->id;
                $metadata['brand_id'] = $geofence->brand_id;
                $metadata['branch_id'] = $geofence->branch_id;
                $branchId = $geofence->branch_id;
                $brandId = $geofence->brand_id;
            }
        }

        // Map event type to Radar event type
        $radarEventType = match($validated['event_type']) {
            'entry' => 'user.entered_geofence',
            'exit' => 'user.exited_geofence',
            'dwell' => 'user.dwelled_in_geofence',
        };

        // Create simulated RadarEventDTO
        $event = new RadarEventDTO(
            eventId: 'simulated_' . uniqid(),
            type: $radarEventType,
            radarUserId: 'simulated_user_' . $validated['user_id'],
            userId: (int) $validated['user_id'],
            geofenceId: $validated['geofence_id'] ? 'simulated_geofence_' . $validated['geofence_id'] : null,
            externalId: $brandId ? "brand_{$brandId}" : null,
            tag: 'simulated',
            lat: (float) $validated['latitude'],
            lng: (float) $validated['longitude'],
            accuracy: 10.0,
            metadata: $metadata,
            occurredAt: now()->toIso8601String(),
        );

        // First check if user should be notified (for debugging info)
        $debugInfo = [];
        if ($brandId) {
            $checkResult = $this->notificationService->shouldNotifyUser(
                $validated['user_id'],
                $brandId,
                $branchId
            );
            $debugInfo['should_notify_check'] = $checkResult;
        }

        // Process the event
        $result = $this->notificationService->processGeofenceEvent($event);

        return response()->json([
            'success' => true,
            'message' => 'Event simulated successfully',
            'event' => $event->toArray(),
            'result' => $result,
            'debug' => $debugInfo,
        ]);
    }

    /**
     * Check notification eligibility for a user/brand combination
     */
    public function checkEligibility(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'brand_id' => 'required|exists:brands,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $result = $this->notificationService->shouldNotifyUser(
            $validated['user_id'],
            $validated['brand_id'],
            $validated['branch_id'] ?? null
        );

        // Get additional debug info
        $user = DB::table('users')
            ->select('id', 'name', 'email', 'fcm_token')
            ->find($validated['user_id']);

        $userInterests = DB::table('interest_user')
            ->join('interests', 'interest_user.interest_id', '=', 'interests.id')
            ->where('interest_user.user_id', $validated['user_id'])
            ->pluck('interests.name')
            ->toArray();

        $brandCategories = DB::table('brand_category')
            ->join('categories', 'brand_category.category_id', '=', 'categories.id')
            ->where('brand_category.brand_id', $validated['brand_id'])
            ->pluck('categories.name')
            ->toArray();

        $recentNotifications = $this->throttleLogRepository->getRecentLogs(5, $validated['user_id']);

        return response()->json([
            'success' => true,
            'eligibility' => $result,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'has_fcm_token' => !empty($user->fcm_token),
            ],
            'user_interests' => $userInterests,
            'brand_categories' => $brandCategories,
            'recent_notifications' => NotificationLogResource::collection($recentNotifications),
            'throttle_config' => [
                'max_per_day' => $this->throttleService->getConfig()->maxPerDay,
                'max_per_week' => $this->throttleService->getConfig()->maxPerWeek,
                'is_quiet_hours' => $this->throttleService->isQuietHours(),
            ],
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total_geofences' => DB::table('geofences')->count(),
            'active_geofences' => DB::table('geofences')->where('is_active', true)->count(),
            'notifications_today' => DB::table('notification_throttle_logs')
                ->where('created_at', '>=', $today)
                ->count(),
            'notifications_this_week' => DB::table('notification_throttle_logs')
                ->where('created_at', '>=', $thisWeek)
                ->count(),
            'notifications_this_month' => DB::table('notification_throttle_logs')
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'unique_users_reached' => DB::table('notification_throttle_logs')
                ->where('created_at', '>=', $thisMonth)
                ->distinct('user_id')
                ->count('user_id'),
            'is_quiet_hours' => $this->throttleService->isQuietHours(),
            'throttle_config' => [
                'max_per_day' => $this->throttleService->getConfig()->maxPerDay,
                'max_per_week' => $this->throttleService->getConfig()->maxPerWeek,
            ],
        ];
    }

    /**
     * Get configuration from database
     */
    private function getConfigFromDatabase(): array
    {
        $dbSettings = DB::table('settings')
            ->where('key', 'like', 'geofence.%')
            ->pluck('value', 'key')
            ->toArray();

        return [
            'radar_secret_key' => $dbSettings['geofence.radar_secret_key'] ?? config('geofence.radar.secret_key', ''),
            'radar_publishable_key' => $dbSettings['geofence.radar_publishable_key'] ?? config('geofence.radar.publishable_key', ''),
            'radar_webhook_secret' => $dbSettings['geofence.radar_webhook_secret'] ?? config('geofence.radar.webhook_secret', ''),
            'max_per_day' => (int)($dbSettings['geofence.max_per_day'] ?? config('geofence.throttle.max_per_day', 5)),
            'max_per_week' => (int)($dbSettings['geofence.max_per_week'] ?? config('geofence.throttle.max_per_week', 15)),
            'min_interval_minutes' => (int)($dbSettings['geofence.min_interval_minutes'] ?? config('geofence.throttle.min_interval_minutes', 30)),
            'brand_cooldown_hours' => (int)($dbSettings['geofence.brand_cooldown_hours'] ?? config('geofence.throttle.brand_cooldown_hours', 24)),
            'location_cooldown_hours' => (int)($dbSettings['geofence.location_cooldown_hours'] ?? config('geofence.throttle.location_cooldown_hours', 4)),
            'offer_cooldown_hours' => (int)($dbSettings['geofence.offer_cooldown_hours'] ?? config('geofence.throttle.offer_cooldown_hours', 48)),
            'quiet_hours_enabled' => (bool)($dbSettings['geofence.quiet_hours_enabled'] ?? config('geofence.throttle.quiet_hours.enabled', true)),
            'quiet_hours_start' => $dbSettings['geofence.quiet_hours_start'] ?? config('geofence.throttle.quiet_hours.start', '22:00'),
            'quiet_hours_end' => $dbSettings['geofence.quiet_hours_end'] ?? config('geofence.throttle.quiet_hours.end', '08:00'),
        ];
    }
}
