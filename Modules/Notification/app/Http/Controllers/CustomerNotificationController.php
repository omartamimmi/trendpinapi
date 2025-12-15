<?php

namespace Modules\Notification\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Notification\app\Models\NotificationDelivery;
use Modules\Notification\app\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Validator;

class CustomerNotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $query = NotificationDelivery::with('notificationMessage')
            ->where('user_id', auth()->id())
            ->whereIn('channel', ['push', 'in_app']) // Only show push and in-app
            ->orderBy('created_at', 'desc');

        // Filter by read/unread
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->paginate(20);

        return response()->json($notifications);
    }

    public function getNotification($id)
    {
        $notification = NotificationDelivery::with('notificationMessage')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Auto-mark as read when viewed
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json($notification);
    }

    public function markAsRead($id)
    {
        $notification = NotificationDelivery::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    public function markAllAsRead()
    {
        $count = NotificationDelivery::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
            ]);

        return response()->json([
            'message' => "{$count} notifications marked as read",
            'count' => $count,
        ]);
    }

    public function deleteNotification($id)
    {
        $notification = NotificationDelivery::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    public function getUnreadCount()
    {
        $count = NotificationDelivery::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // User Preferences
    public function getPreferences()
    {
        $preferences = UserNotificationPreference::where('user_id', auth()->id())->get();

        // Define all available channels and tags
        $channels = ['push', 'sms', 'email'];
        $tags = ['nearby', 'new_offer', 'offer_expiring', 'brand_update', 'promotional', 'system'];

        // Build preference matrix
        $preferencesMatrix = [];
        foreach ($channels as $channel) {
            foreach ($tags as $tag) {
                $pref = $preferences->where('channel', $channel)->where('tag', $tag)->first();
                $preferencesMatrix[$channel][$tag] = $pref ? $pref->is_enabled : true;
            }
        }

        return response()->json([
            'preferences' => $preferencesMatrix,
            'channels' => $channels,
            'tags' => $tags,
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.*.channel' => 'required|in:push,sms,email',
            'preferences.*.tag' => 'required|string',
            'preferences.*.enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->preferences as $pref) {
            UserNotificationPreference::setPreference(
                auth()->id(),
                $pref['channel'],
                $pref['tag'],
                $pref['enabled']
            );
        }

        return response()->json(['message' => 'Preferences updated successfully']);
    }

    public function updateFCMToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        \DB::table('notification_based_location')->updateOrInsert(
            ['user_id' => auth()->id()],
            [
                'fcm_token' => $request->fcm_token,
                'lat' => $request->lat ?? null,
                'lng' => $request->lng ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'FCM token updated successfully']);
    }
}
