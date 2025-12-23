<?php

namespace Modules\Geofence\Services\Contracts;

use Modules\Geofence\DTO\RadarEventDTO;

interface GeofenceNotificationServiceInterface
{
    /**
     * Process a geofence event from Radar.io
     */
    public function processGeofenceEvent(RadarEventDTO $event): array;

    /**
     * Send notification to user about an offer
     */
    public function sendOfferNotification(int $userId, object $offer, int $branchId): bool;

    /**
     * Check if user should receive notification for this brand
     */
    public function shouldNotifyUser(int $userId, int $brandId, ?int $branchId = null): array;
}
