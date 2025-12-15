<?php

namespace Modules\Geofence\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Geofence\app\Models\UserLocation;
use Modules\Geofence\Repositories\Contracts\UserLocationRepositoryInterface;

class UserLocationRepository implements UserLocationRepositoryInterface
{
    private const EARTH_RADIUS_KM = 6371;

    public function __construct(
        protected UserLocation $model
    ) {}

    public function findByUserId(int $userId): ?UserLocation
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function findByRadarUserId(string $radarUserId): ?UserLocation
    {
        return $this->model->where('radar_user_id', $radarUserId)->first();
    }

    public function updateOrCreate(int $userId, array $data): UserLocation
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            array_merge($data, ['location_updated_at' => now()])
        );
    }

    public function updateLocation(int $userId, float $lat, float $lng, ?float $accuracy = null): UserLocation
    {
        return $this->updateOrCreate($userId, [
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $accuracy,
        ]);
    }

    public function updateFcmToken(int $userId, string $fcmToken): UserLocation
    {
        return $this->updateOrCreate($userId, [
            'fcm_token' => $fcmToken,
        ]);
    }

    public function getUsersWithinRadius(float $lat, float $lng, float $radiusKm): Collection
    {
        return $this->model->newQuery()
            ->trackingEnabled()
            ->withFcmToken()
            ->select('*')
            ->selectRaw("
                (? * acos(
                    cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )) AS distance
            ", [self::EARTH_RADIUS_KM, $lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radiusKm])
            ->orderBy('distance')
            ->get();
    }

    public function getTrackingEnabledUsers(): Collection
    {
        return $this->model->trackingEnabled()->withFcmToken()->get();
    }
}
