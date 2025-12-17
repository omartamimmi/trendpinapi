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

class GeofenceNotificationService implements GeofenceNotificationServiceInterface
{
    private FcmService $fcmService;

    public function __construct(
        private ThrottleServiceInterface $throttleService,
        private InterestMatchingServiceInterface $interestMatchingService,
        private GeofenceRepositoryInterface $geofenceRepository,
        private UserLocationRepositoryInterface $userLocationRepository,
        private ThrottleLogRepositoryInterface $throttleLogRepository,
    ) {
        $this->fcmService = new FcmService();
    }

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
        $locationId = $event->getLocationId();

        // If we have a geofence DB ID, get location/brand from there
        if ($geofenceDbId && !$brandId && !$locationId) {
            $geofence = $this->geofenceRepository->find($geofenceDbId);
            if ($geofence) {
                $locationId = $geofence->location_id;
                $brandId = $geofence->brand_id;
                $branchId = $branchId ?: $geofence->branch_id;
            }
        }

        // If this is a location-based geofence, handle differently
        if ($locationId) {
            return $this->processLocationEvent($userId, $locationId, $event, $geofenceDbId);
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
     * Process a location-based geofence event (mall, shopping district, etc.)
     * Finds all branches at the location and sends the best matching offer
     */
    private function processLocationEvent(int $userId, int $locationId, RadarEventDTO $event, ?int $geofenceDbId): array
    {
        $result = [
            'processed' => true,
            'notification_sent' => false,
            'reason' => null,
            'location_id' => $locationId,
        ];

        // Get all branches at this location
        $branches = DB::table('branches')
            ->where('location_id', $locationId)
            ->where('status', 'publish')
            ->select('id', 'brand_id', 'name')
            ->get();

        if ($branches->isEmpty()) {
            $result['reason'] = 'no_branches_at_location';
            return $result;
        }

        // Get unique brand IDs
        $brandIds = $branches->pluck('brand_id')->unique()->filter()->values()->toArray();

        if (empty($brandIds)) {
            $result['reason'] = 'no_brands_at_location';
            return $result;
        }

        // Check user preferences
        $user = DB::table('users')->find($userId);
        if (!$user) {
            $result['reason'] = 'user_not_found';
            return $result;
        }

        if (isset($user->notifications_enabled) && !$user->notifications_enabled) {
            $result['reason'] = 'notifications_disabled';
            return $result;
        }

        // Find matching brands based on user interests
        $matchingBrands = [];
        foreach ($brandIds as $brandId) {
            if ($this->interestMatchingService->userMatchesBrand($userId, $brandId)) {
                $matchingBrands[] = $brandId;
            }
        }

        if (empty($matchingBrands)) {
            $result['reason'] = 'no_interest_match_at_location';
            return $result;
        }

        // Find best offer from all matching brands
        $bestOffer = null;
        $bestBrandId = null;
        $bestBranchId = null;

        foreach ($matchingBrands as $brandId) {
            // Check throttling for this brand
            $throttleReason = $this->throttleService->canSendNotification($userId, $brandId, null, null);
            if ($throttleReason) {
                continue; // Skip throttled brands
            }

            $offer = $this->interestMatchingService->getBestMatchingOffer($userId, $brandId);
            if ($offer) {
                // Compare offers - prefer higher discount value
                if (!$bestOffer || ($offer->discount_value ?? 0) > ($bestOffer->discount_value ?? 0)) {
                    $bestOffer = $offer;
                    $bestBrandId = $brandId;
                    // Find branch for this brand at this location
                    $branch = $branches->firstWhere('brand_id', $brandId);
                    $bestBranchId = $branch ? $branch->id : null;
                }
            }
        }

        if (!$bestOffer) {
            $result['reason'] = 'no_matching_offers_at_location';
            return $result;
        }

        // Send notification
        $sent = $this->sendOfferNotification($userId, $bestOffer, $bestBranchId ?? 0);

        if ($sent) {
            // Log the notification
            $this->throttleLogRepository->create([
                'user_id' => $userId,
                'brand_id' => $bestBrandId,
                'branch_id' => $bestBranchId,
                'offer_id' => $bestOffer->id,
                'geofence_id' => $geofenceDbId,
                'event_type' => 'entry',
                'latitude' => $event->lat,
                'longitude' => $event->lng,
                'radar_event_id' => $event->eventId,
            ]);

            $result['notification_sent'] = true;
            $result['brand_id'] = $bestBrandId;
            $result['offer_id'] = $bestOffer->id;
            $result['branches_at_location'] = $branches->count();
            $result['matching_brands'] = count($matchingBrands);
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

            // Build notification content using templates or fallback
            $content = $this->buildNotificationContent($offer, $brand);

            // Prepare notification data
            $notificationData = [
                'title' => $content['title'],
                'body' => $content['body'],
                'data' => [
                    'type' => 'geofence_offer',
                    'offer_id' => (string) $offer->id,
                    'brand_id' => (string) $offer->brand_id,
                    'branch_id' => (string) $branchId,
                    'deep_link' => $content['deep_link'] ?? "trendpin://offer/{$offer->id}",
                ],
            ];

            // Send FCM notification
            $sent = $this->sendFcmNotification($user->fcm_token, $notificationData);

            if ($sent) {
                Log::info('Geofence notification sent', [
                    'user_id' => $userId,
                    'offer_id' => $offer->id,
                    'brand_id' => $offer->brand_id,
                    'template_used' => $this->getNotificationTemplate($offer) !== null,
                ]);
            }

            return $sent;
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
     * Build notification content using templates or fallback to programmatic
     */
    private function buildNotificationContent(object $offer, ?object $brand = null): array
    {
        // Try to use template system first
        $template = $this->getNotificationTemplate($offer);

        if ($template) {
            $data = [
                'brand_name' => $brand->name ?? 'Special Offer',
                'offer_name' => $offer->name ?? '',
                'offer_description' => $this->truncateText($offer->description ?? '', 50),
                'discount_value' => $offer->discount_value ?? '',
                'offer_id' => $offer->id,
                'brand_id' => $offer->brand_id,
            ];

            return $this->renderTemplate($template, $data);
        }

        // Fallback to programmatic approach
        return [
            'title' => $brand->name ?? 'Special Offer',
            'body' => $this->buildNotificationBody($offer),
            'deep_link' => "trendpin://offer/{$offer->id}",
        ];
    }

    /**
     * Render a notification template with data
     */
    private function renderTemplate(object $template, array $data): array
    {
        $title = $template->title_template;
        $body = $template->body_template;
        $deepLink = $template->deep_link_template;

        foreach ($data as $key => $value) {
            $placeholder = "{{" . $key . "}}";
            $title = str_replace($placeholder, (string) $value, $title);
            $body = str_replace($placeholder, (string) $value, $body);
            if ($deepLink) {
                $deepLink = str_replace($placeholder, (string) $value, $deepLink);
            }
        }

        return [
            'title' => $title,
            'body' => $body,
            'deep_link' => $deepLink,
        ];
    }

    /**
     * Get appropriate notification template based on offer type
     */
    private function getNotificationTemplate(object $offer): ?object
    {
        $tag = match($offer->discount_type ?? 'percentage') {
            'percentage' => 'geofence_offer',
            'fixed' => 'geofence_offer_fixed',
            'bogo' => 'geofence_offer_bogo',
            default => 'geofence_offer',
        };

        return DB::table('notification_templates')
            ->where('tag', $tag)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Build notification body text (fallback method)
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
            $parts[] = $this->truncateText($offer->description, 50);
        }

        return implode(' - ', $parts) ?: 'Check out this special offer!';
    }

    /**
     * Truncate text to specified length
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - 3) . '...';
    }

    /**
     * Send FCM notification using the FcmService
     */
    private function sendFcmNotification(string $fcmToken, array $notificationData): bool
    {
        return $this->fcmService->send($fcmToken, $notificationData);
    }

    /**
     * Test FCM configuration (for debugging)
     */
    public function testFcmConfiguration(): array
    {
        return $this->fcmService->testConfiguration();
    }
}
