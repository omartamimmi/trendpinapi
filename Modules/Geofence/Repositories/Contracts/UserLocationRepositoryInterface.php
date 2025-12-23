<?php

namespace Modules\Geofence\Repositories\Contracts;

use Illuminate\Support\Collection;
use Modules\Geofence\app\Models\UserLocation;

interface UserLocationRepositoryInterface
{
    public function findByUserId(int $userId): ?UserLocation;

    public function findByRadarUserId(string $radarUserId): ?UserLocation;

    public function updateOrCreate(int $userId, array $data): UserLocation;

    public function updateLocation(int $userId, float $lat, float $lng, ?float $accuracy = null): UserLocation;

    public function updateFcmToken(int $userId, string $fcmToken): UserLocation;

    public function getUsersWithinRadius(float $lat, float $lng, float $radiusKm): Collection;

    public function getTrackingEnabledUsers(): Collection;
}
