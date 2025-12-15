<?php

namespace Modules\Geofence\Services;

use Modules\Geofence\Services\Contracts\GeofenceNotificationServiceInterface;
use Modules\Geofence\Services\Contracts\ThrottleServiceInterface;
use Modules\Geofence\Services\Contracts\InterestMatchingServiceInterface;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Modules\Geofence\Repositories\Contracts\UserLocationRepositoryInterface;
use Modules\Geofence\Repositories\Contracts\ThrottleLogRepositoryInterface;
use Modules\Geofence\DTO\RadarEventDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class GeofenceNotificationService implements GeofenceNotificationServiceInterface
{
    public function __construct(
        private ThrottleServiceInterface $throttleService,
        private InterestMatchingServiceInterface $interestMatchingService,
        private GeofenceRepositoryInterface $geofenceRepository,
        private UserLocationRepositoryInterface $userLocationRepository,
        private ThrottleLogRepositoryInterface $throttleLogRepository,
    ) {}

    /**
     * Process a geofence event from Radar.io
     */
    public function processGeofenceEvent(RadarEventDTO $event): array
    {
        $result = [
            'processed' => false,
            'notification_sent' => false,
            'reason' => null,
        ];

        // Only process entry events for notifications
        if (!$event->isGeofenceEntry()) {
            $result['reason'] = 'not_an_entry_event';
            return $result;
        }

        // Get user ID
        $userId = $event->userId;
        if (!$userId) {
            $result['reason'] = 'no_user_id';
            return $result;
        }

        // Update user location
        $this->userLocationRepository->updateLocation(
            $userId,
            $event->lat,
            $event->lng,
            $event->accuracy
        );

        // Get branch/brand from event
        $branchId = $event->getBranchId();
        $brandId = $event->getBrandId();
        $geofenceDbId = $event->getGeofenceDbId();

        // If we have a geofence DB ID, get brand from there
        if ($geofenceDbId && !$brandId) {
            $geofence = $this->geofenceRepository->find($geofenceDbId);
            if ($geofence) {
                $brandId = $geofence->brand_id;
                $branchId = $branchId ?: $geofence->branch_id;
            }
        }

        // If we have branch but no brand, get brand from branch
        if ($branchId && !$brandId) {
            $branch = DB::table('branches')->find($branchId);
            if ($branch) {
                $brandId = $branch->brand_id;
            }
        }

        if (!$brandId) {
            $result['reason'] = 'no_brand_identified';
            return $result;
        }

        $result['processed'] = true;

        // Check if user should receive notification
        $notificationCheck = $this->shouldNotifyUser($userId, $brandId, $branchId);

        if (!$notificationCheck['should_notify']) {
            $result['reason'] = $notificationCheck['reason'];
            return $result;
        }

        // Get best matching offer
        $offer = $notificationCheck['offer'];

        if (!$offer) {
            $result['reason'] = 'no_matching_offer';
            return $result;
        }

        // Send notification
        $sent = $this->sendOfferNotification($userId, $offer, $branchId ?? 0);

        if ($sent) {
            // Log the notification
            $this->throttleLogRepository->create([
                'user_id' => $userId,
                'brand_id' => $brandId,
                'branch_id' => $branchId,
                'offer_id' => $offer->id,
                'geofence_id' => $geofenceDbId,
                'event_type' => 'entry',
                'latitude' => $event->lat,
                'longitude' => $event->lng,
                'radar_event_id' => $event->eventId,
            ]);

            $result['notification_sent'] = true;
        } else {
            $result['reason'] = 'notification_send_failed';
        }

        return $result;
    }

    /**
     * Check if user should receive notification for this brand
     */
    public function shouldNotifyUser(int $userId, int $brandId, ?int $branchId = null): array
    {
        $result = [
            'should_notify' => false,
            'reason' => null,
            'offer' => null,
        ];

        // Check user notification preferences
        $user = DB::table('users')->find($userId);
        if (!$user) {
            $result['reason'] = 'user_not_found';
            return $result;
        }

        // Check if user has notifications enabled
        if (isset($user->notifications_enabled) && !$user->notifications_enabled) {
            $result['reason'] = 'notifications_disabled';
            return $result;
        }

        // Check interest matching
        if (!$this->interestMatchingService->userMatchesBrand($userId, $brandId)) {
            $result['reason'] = 'no_interest_match';
            return $result;
        }

        // Get best matching offer first (before throttle check)
        $offer = $this->interestMatchingService->getBestMatchingOffer($userId, $brandId);

        if (!$offer) {
            $result['reason'] = 'no_matching_offers';
            return $result;
        }

        // Check throttling (pass offer ID for offer-specific cooldown)
        $throttleReason = $this->throttleService->canSendNotification(
            $userId,
            $brandId,
            $branchId,
            $offer->id ?? null
        );

        if ($throttleReason) {
            $result['reason'] = $throttleReason;
            return $result;
        }

        $result['should_notify'] = true;
        $result['offer'] = $offer;

        return $result;
    }

    /**
     * Send notification to user about an offer
     */
    public function sendOfferNotification(int $userId, object $offer, int $branchId): bool
    {
        try {
            // Get user with FCM token
            $user = DB::table('users')
                ->select('id', 'name', 'email', 'fcm_token')
                ->find($userId);

            if (!$user || !$user->fcm_token) {
                Log::warning('Cannot send notification: no FCM token', [
                    'user_id' => $userId,
                ]);
                return false;
            }

            // Get brand info
            $brand = DB::table('brands')
                ->select('id', 'name', 'logo')
                ->find($offer->brand_id);

            // Prepare notification data
            $notificationData = [
                'title' => $brand->name ?? 'Special Offer',
                'body' => $this->buildNotificationBody($offer),
                'data' => [
                    'type' => 'geofence_offer',
                    'offer_id' => $offer->id,
                    'brand_id' => $offer->brand_id,
                    'branch_id' => $branchId,
                ],
            ];

            // Send FCM notification
            $this->sendFcmNotification($user->fcm_token, $notificationData);

            Log::info('Geofence notification sent', [
                'user_id' => $userId,
                'offer_id' => $offer->id,
                'brand_id' => $offer->brand_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send geofence notification', [
                'user_id' => $userId,
                'offer_id' => $offer->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build notification body text
     */
    private function buildNotificationBody(object $offer): string
    {
        $parts = [];

        if (!empty($offer->name)) {
            $parts[] = $offer->name;
        }

        // Build discount text based on discount_type and discount_value
        if (!empty($offer->discount_value)) {
            $discountText = match($offer->discount_type ?? 'percentage') {
                'percentage' => "{$offer->discount_value}% off",
                'fixed' => "\${$offer->discount_value} off",
                'bogo' => 'Buy One Get One',
                default => "{$offer->discount_value}% off",
            };
            $parts[] = $discountText;
        }

        if (!empty($offer->description)) {
            $description = strlen($offer->description) > 50
                ? substr($offer->description, 0, 47) . '...'
                : $offer->description;
            $parts[] = $description;
        }

        return implode(' - ', $parts) ?: 'Check out this special offer!';
    }

    /**
     * Send FCM notification
     */
    private function sendFcmNotification(string $fcmToken, array $notificationData): void
    {
        $serverKey = config('services.firebase.server_key');

        if (!$serverKey) {
            Log::warning('Firebase server key not configured');
            return;
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $notificationData['title'],
                'body' => $notificationData['body'],
                'sound' => 'default',
            ],
            'data' => $notificationData['data'],
            'priority' => 'high',
        ]);

        if (!$response->successful()) {
            Log::error('FCM notification failed', [
                'response' => $response->json(),
            ]);
        }
    }
}
