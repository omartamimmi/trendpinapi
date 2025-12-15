<?php

namespace Modules\Notification\app\Services;

use Modules\Notification\app\Models\NotificationProvider;
use Modules\Notification\app\Models\NotificationMessage;
use Modules\Notification\app\Models\NotificationDelivery;
use Modules\Notification\app\Models\UserNotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendNotification(NotificationMessage $notification)
    {
        // Get target users based on criteria
        $users = $this->getTargetUsers($notification);

        if ($users->isEmpty()) {
            $notification->update([
                'status' => 'failed',
                'delivery_stats' => ['error' => 'No target users found'],
            ]);
            return;
        }

        $notification->update([
            'status' => 'sending',
            'total_recipients' => $users->count(),
            'sent_at' => now(),
        ]);

        $stats = [];

        foreach ($notification->channels as $channel) {
            $channelStats = $this->sendToChannel($notification, $users, $channel);
            $stats[$channel] = $channelStats;
        }

        $notification->update([
            'status' => 'sent',
            'delivery_stats' => $stats,
        ]);
    }

    protected function getTargetUsers(NotificationMessage $notification)
    {
        $query = User::query();

        switch ($notification->target_type) {
            case 'all':
                // All active users
                $query->whereNotNull('id');
                break;

            case 'location':
                // Users within radius of a location
                $criteria = $notification->target_criteria;
                if (isset($criteria['lat'], $criteria['lng'], $criteria['radius'])) {
                    $query->select('users.*')
                        ->join('notification_based_location', 'users.id', '=', 'notification_based_location.user_id')
                        ->selectRaw(
                            '( 6371 * acos( cos( radians(?) ) * cos( radians( CAST(notification_based_location.lat AS DECIMAL(10,8)) ) ) * cos( radians( CAST(notification_based_location.lng AS DECIMAL(11,8)) ) - radians(?) ) + sin( radians(?) ) * sin( radians( CAST(notification_based_location.lat AS DECIMAL(10,8)) ) ) ) ) AS distance',
                            [$criteria['lat'], $criteria['lng'], $criteria['lat']]
                        )
                        ->havingRaw('distance < ?', [$criteria['radius']])
                        ->distinct();
                }
                break;

            case 'individual':
                // Specific user IDs
                if (isset($notification->target_criteria['user_ids'])) {
                    $query->whereIn('id', $notification->target_criteria['user_ids']);
                }
                break;

            case 'segment':
                // Custom user segments (can be expanded)
                // Example: active users, new users, etc.
                break;
        }

        return $query->get();
    }

    protected function sendToChannel(NotificationMessage $notification, $users, $channel)
    {
        $provider = NotificationProvider::where('type', $channel)
            ->active()
            ->primary()
            ->first();

        if (!$provider) {
            Log::warning("No active provider found for channel: {$channel}");
            return ['sent' => 0, 'failed' => $users->count(), 'error' => 'No provider'];
        }

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            // Check user preferences
            if (!UserNotificationPreference::isEnabled($user->id, $channel, $notification->tag)) {
                continue;
            }

            $delivery = NotificationDelivery::create([
                'notification_message_id' => $notification->id,
                'user_id' => $user->id,
                'channel' => $channel,
                'provider_id' => $provider->id,
                'status' => 'pending',
            ]);

            try {
                $result = $this->sendViaProvider($provider, $user, $notification, $channel);

                if ($result['success']) {
                    $delivery->update([
                        'status' => 'sent',
                        'provider_message_id' => $result['message_id'],
                        'provider_response' => json_encode($result['response']),
                        'sent_at' => now(),
                    ]);
                    $sent++;
                } else {
                    $delivery->update([
                        'status' => 'failed',
                        'failed_reason' => $result['error'] ?? 'Unknown error',
                        'provider_response' => json_encode($result['response'] ?? []),
                    ]);
                    $failed++;
                }
            } catch (\Exception $e) {
                $delivery->update([
                    'status' => 'failed',
                    'failed_reason' => $e->getMessage(),
                ]);
                $failed++;
                Log::error("Notification delivery failed: {$e->getMessage()}");
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    protected function sendViaProvider($provider, $user, $notification, $channel)
    {
        $providerService = $this->getProviderService($provider);

        $recipient = $this->getRecipient($user, $channel);
        if (!$recipient) {
            return ['success' => false, 'error' => 'No recipient address'];
        }

        $message = [
            'title' => $notification->title,
            'body' => $notification->body,
            'image_url' => $notification->image_url,
            'data' => array_merge(
                $notification->action_data ?? [],
                [
                    'tag' => $notification->tag,
                    'deep_link' => $notification->deep_link,
                    'notification_id' => $notification->id,
                ]
            ),
        ];

        return $providerService->send($recipient, $message);
    }

    protected function getProviderService(NotificationProvider $provider)
    {
        $class = 'Modules\\Notification\\app\\Services\\' . ucfirst($provider->provider) . 'Provider';

        if (!class_exists($class)) {
            throw new \Exception("Provider class not found: {$class}");
        }

        return new $class($provider->credentials);
    }

    protected function getRecipient($user, $channel)
    {
        switch ($channel) {
            case 'push':
                // Get FCM token from notification_based_location or user table
                $location = DB::table('notification_based_location')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();
                return $location->fcm_token ?? null;

            case 'sms':
                return $user->phone ?? null;

            case 'email':
                return $user->email ?? null;

            default:
                return null;
        }
    }
}
