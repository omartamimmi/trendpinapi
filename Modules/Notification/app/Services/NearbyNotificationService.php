<?php

namespace Modules\Notification\app\Services;

use Modules\Notification\app\Models\NotificationMessage;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
use Illuminate\Support\Facades\DB;

class NearbyNotificationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendNearbyBrandsNotification($userLat, $userLng, $radiusKm = 5, $channels = ['push'])
    {
        // Find branches within radius
        $branches = $this->getBranchesNearby($userLat, $userLng, $radiusKm);

        if ($branches->isEmpty()) {
            return ['success' => false, 'message' => 'No branches found nearby'];
        }

        $brandCount = $branches->pluck('brand_id')->unique()->count();

        // Create notification message
        $notification = NotificationMessage::create([
            'tag' => 'nearby',
            'title' => "Discover {$brandCount} brands near you!",
            'body' => "Check out great offers from brands within {$radiusKm}km of your location",
            'channels' => $channels,
            'target_type' => 'location',
            'target_criteria' => [
                'lat' => $userLat,
                'lng' => $userLng,
                'radius' => $radiusKm,
            ],
            'action_data' => [
                'type' => 'branches',
                'branch_ids' => $branches->pluck('id')->toArray(),
                'brand_ids' => $branches->pluck('brand_id')->unique()->toArray(),
            ],
            'deep_link' => 'app://brands/nearby',
            'status' => 'draft',
        ]);

        // Send notification
        $this->notificationService->sendNotification($notification);

        return [
            'success' => true,
            'notification' => $notification,
            'branches_count' => $branches->count(),
            'brands_count' => $brandCount,
        ];
    }

    public function sendNearbyOffersNotification($userLat, $userLng, $radiusKm = 5, $channels = ['push'])
    {
        // This would integrate with your offers system
        // For now, just find brands with branches nearby
        $branches = $this->getBranchesNearby($userLat, $userLng, $radiusKm);

        if ($branches->isEmpty()) {
            return ['success' => false, 'message' => 'No offers found nearby'];
        }

        // You would query offers table here
        // For now, assuming brands have offers
        $brandIds = $branches->pluck('brand_id')->unique();

        $notification = NotificationMessage::create([
            'tag' => 'nearby',
            'title' => 'New offers near you!',
            'body' => "Don't miss out on exclusive deals from brands in your area",
            'channels' => $channels,
            'target_type' => 'location',
            'target_criteria' => [
                'lat' => $userLat,
                'lng' => $userLng,
                'radius' => $radiusKm,
            ],
            'action_data' => [
                'type' => 'offers',
                'brand_ids' => $brandIds->toArray(),
            ],
            'deep_link' => 'app://offers/nearby',
            'status' => 'draft',
        ]);

        $this->notificationService->sendNotification($notification);

        return [
            'success' => true,
            'notification' => $notification,
            'brands_count' => $brandIds->count(),
        ];
    }

    protected function getBranchesNearby($lat, $lng, $radiusKm)
    {
        return Branch::select('branches.*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( CAST(brands.lat AS DECIMAL(10,8)) ) ) * cos( radians( CAST(brands.lng AS DECIMAL(11,8)) ) - radians(?) ) + sin( radians(?) ) * sin( radians( CAST(brands.lat AS DECIMAL(10,8)) ) ) ) ) AS distance',
                [$lat, $lng, $lat]
            )
            ->join('brands', 'branches.brand_id', '=', 'brands.id')
            ->whereNotNull('brands.lat')
            ->whereNotNull('brands.lng')
            ->havingRaw('distance < ?', [$radiusKm])
            ->orderBy('distance')
            ->get();
    }
}
