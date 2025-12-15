<?php

namespace Modules\Geofence\Services\Contracts;

use Modules\Geofence\app\Models\Geofence;
use Illuminate\Support\Collection;

interface RadarServiceInterface
{
    /**
     * Create a geofence in Radar.io
     */
    public function createGeofence(Geofence $geofence): ?string;

    /**
     * Update a geofence in Radar.io
     */
    public function updateGeofence(Geofence $geofence): bool;

    /**
     * Delete a geofence from Radar.io
     */
    public function deleteGeofence(string $radarGeofenceId): bool;

    /**
     * Sync all local geofences to Radar.io
     */
    public function syncAllGeofences(): array;

    /**
     * Get all geofences from Radar.io
     */
    public function listGeofences(?string $tag = null): array;

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Create or update user in Radar.io
     */
    public function upsertUser(int $userId, array $metadata = []): ?string;
}
