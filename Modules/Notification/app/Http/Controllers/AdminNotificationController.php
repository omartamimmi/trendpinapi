<?php

namespace Modules\Notification\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Notification\app\Models\NotificationMessage;
use Modules\Notification\app\Models\NotificationProvider;
use Modules\Notification\app\Models\NotificationTemplate;
use Modules\Notification\app\Services\NotificationService;
use Illuminate\Support\Facades\Validator;

class AdminNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // Providers Management
    public function getProviders()
    {
        $providers = NotificationProvider::orderBy('type')->orderBy('priority')->get();
        return response()->json($providers);
    }

    public function storeProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:push,sms,email,whatsapp',
            'provider' => 'required|string',
            'name' => 'required|string',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider = NotificationProvider::create($request->all());

        return response()->json([
            'message' => 'Provider created successfully',
            'provider' => $provider,
        ], 201);
    }

    public function updateProvider(Request $request, $id)
    {
        $provider = NotificationProvider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:push,sms,email,whatsapp',
            'provider' => 'sometimes|string',
            'name' => 'sometimes|string',
            'credentials' => 'sometimes|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider->update($request->all());

        return response()->json([
            'message' => 'Provider updated successfully',
            'provider' => $provider,
        ]);
    }

    public function testProvider($id)
    {
        $provider = NotificationProvider::findOrFail($id);

        $class = 'Modules\\Notification\\app\\Services\\' . ucfirst($provider->provider) . 'Provider';

        if (!class_exists($class)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider class not found',
            ], 404);
        }

        $providerService = new $class($provider->credentials);
        $result = $providerService->validateCredentials();

        $provider->update([
            'last_tested_at' => now(),
            'last_test_result' => json_encode($result),
        ]);

        return response()->json($result);
    }

    public function deleteProvider($id)
    {
        $provider = NotificationProvider::findOrFail($id);
        $provider->delete();

        return response()->json(['message' => 'Provider deleted successfully']);
    }

    // Notification Messages
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'channels' => 'required|array',
            'channels.*' => 'in:push,sms,email',
            'target_type' => 'required|in:all,location,individual,segment',
            'target_criteria' => 'sometimes|array',
            'scheduled_at' => 'sometimes|date|after:now',
            'image_url' => 'sometimes|url',
            'deep_link' => 'sometimes|string',
            'action_data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $notification = NotificationMessage::create(array_merge(
            $request->all(),
            [
                'status' => $request->has('scheduled_at') ? 'scheduled' : 'draft',
                'created_by' => auth()->id(),
            ]
        ));

        // Send immediately if not scheduled
        if (!$request->has('scheduled_at')) {
            $this->notificationService->sendNotification($notification);
        }

        return response()->json([
            'message' => 'Notification created successfully',
            'notification' => $notification->load('deliveries'),
        ], 201);
    }

    public function getNotifications(Request $request)
    {
        $query = NotificationMessage::with('creator');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('tag')) {
            $query->where('tag', $request->tag);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($notifications);
    }

    public function getNotification($id)
    {
        $notification = NotificationMessage::with(['deliveries.user', 'creator'])
            ->findOrFail($id);

        return response()->json($notification);
    }

    public function getNotificationStats($id)
    {
        $notification = NotificationMessage::with('deliveries')->findOrFail($id);

        $stats = [
            'total' => $notification->deliveries->count(),
            'sent' => $notification->deliveries->where('status', 'sent')->count(),
            'delivered' => $notification->deliveries->where('status', 'delivered')->count(),
            'read' => $notification->deliveries->whereNotNull('read_at')->count(),
            'clicked' => $notification->deliveries->whereNotNull('clicked_at')->count(),
            'failed' => $notification->deliveries->where('status', 'failed')->count(),
            'by_channel' => [],
        ];

        foreach ($notification->channels as $channel) {
            $stats['by_channel'][$channel] = [
                'total' => $notification->deliveries->where('channel', $channel)->count(),
                'sent' => $notification->deliveries->where('channel', $channel)->where('status', 'sent')->count(),
                'failed' => $notification->deliveries->where('channel', $channel)->where('status', 'failed')->count(),
            ];
        }

        return response()->json($stats);
    }

    // Templates
    public function getTemplates()
    {
        $templates = NotificationTemplate::orderBy('created_at', 'desc')->get();
        return response()->json($templates);
    }

    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'tag' => 'required|string',
            'title_template' => 'required|string',
            'body_template' => 'required|string',
            'action_type' => 'sometimes|string',
            'action_data' => 'sometimes|array',
            'image_url' => 'sometimes|url',
            'deep_link_template' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template = NotificationTemplate::create(array_merge(
            $request->all(),
            ['created_by' => auth()->id()]
        ));

        return response()->json([
            'message' => 'Template created successfully',
            'template' => $template,
        ], 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'tag' => 'sometimes|string',
            'title_template' => 'sometimes|string',
            'body_template' => 'sometimes|string',
            'action_type' => 'sometimes|string',
            'action_data' => 'sometimes|array',
            'image_url' => 'sometimes|url',
            'deep_link_template' => 'sometimes|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template->update($request->all());

        return response()->json([
            'message' => 'Template updated successfully',
            'template' => $template,
        ]);
    }

    public function deleteTemplate($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }
}
