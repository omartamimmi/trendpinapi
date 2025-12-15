<?php

namespace Modules\Notification\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Notification\app\Models\NotificationSetting;

class NotificationSettingsController extends Controller
{
    /**
     * Get all notification settings
     */
    public function index(): JsonResponse
    {
        $settings = NotificationSetting::orderBy('category')->orderBy('name')->get();

        // Transform to frontend format
        $events = $settings->map(function ($setting) {
            return [
                'id' => $setting->event_id,
                'name' => $setting->name,
                'description' => $setting->description,
                'category' => $setting->category,
                'isEnabled' => $setting->is_enabled,
                'recipients' => $setting->recipients,
                'channels' => $setting->channels,
            ];
        });

        $templates = $settings->map(function ($setting) {
            return [
                'id' => 'tpl_' . $setting->event_id,
                'eventId' => $setting->event_id,
                'name' => $setting->name,
                'description' => $setting->description,
                'category' => $setting->category,
                'templates' => $setting->templates,
                'placeholders' => $setting->placeholders,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
                'templates' => $templates,
            ],
        ]);
    }

    /**
     * Save all notification settings (bulk update)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.id' => 'required|string',
            'events.*.name' => 'required|string',
            'events.*.description' => 'nullable|string',
            'events.*.category' => 'required|string',
            'events.*.isEnabled' => 'required|boolean',
            'events.*.recipients' => 'required|array',
            'events.*.channels' => 'required|array',
            'templates' => 'required|array',
            'templates.*.id' => 'required|string',
            'templates.*.eventId' => 'required|string',
            'templates.*.templates' => 'required|array',
            'templates.*.placeholders' => 'required|array',
        ]);

        try {
            // Index templates by eventId for quick lookup
            $templatesById = collect($validated['templates'])->keyBy('eventId');

            foreach ($validated['events'] as $event) {
                $template = $templatesById->get($event['id']);

                NotificationSetting::updateOrCreate(
                    ['event_id' => $event['id']],
                    [
                        'name' => $event['name'],
                        'description' => $event['description'] ?? '',
                        'category' => $event['category'],
                        'is_enabled' => $event['isEnabled'],
                        'recipients' => $event['recipients'],
                        'channels' => $event['channels'],
                        'templates' => $template['templates'] ?? [],
                        'placeholders' => $template['placeholders'] ?? [],
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification settings saved successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single notification setting
     */
    public function show(string $eventId): JsonResponse
    {
        $setting = NotificationSetting::where('event_id', $eventId)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => [
                    'id' => $setting->event_id,
                    'name' => $setting->name,
                    'description' => $setting->description,
                    'category' => $setting->category,
                    'isEnabled' => $setting->is_enabled,
                    'recipients' => $setting->recipients,
                    'channels' => $setting->channels,
                ],
                'template' => [
                    'id' => 'tpl_' . $setting->event_id,
                    'eventId' => $setting->event_id,
                    'name' => $setting->name,
                    'description' => $setting->description,
                    'category' => $setting->category,
                    'templates' => $setting->templates,
                    'placeholders' => $setting->placeholders,
                ],
            ],
        ]);
    }

    /**
     * Update a single notification setting
     */
    public function update(Request $request, string $eventId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'category' => 'sometimes|string',
            'isEnabled' => 'sometimes|boolean',
            'recipients' => 'sometimes|array',
            'channels' => 'sometimes|array',
            'templates' => 'sometimes|array',
            'placeholders' => 'sometimes|array',
        ]);

        try {
            $setting = NotificationSetting::where('event_id', $eventId)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found',
                ], 404);
            }

            // Map frontend keys to database keys
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['description'])) $updateData['description'] = $validated['description'];
            if (isset($validated['category'])) $updateData['category'] = $validated['category'];
            if (isset($validated['isEnabled'])) $updateData['is_enabled'] = $validated['isEnabled'];
            if (isset($validated['recipients'])) $updateData['recipients'] = $validated['recipients'];
            if (isset($validated['channels'])) $updateData['channels'] = $validated['channels'];
            if (isset($validated['templates'])) $updateData['templates'] = $validated['templates'];
            if (isset($validated['placeholders'])) $updateData['placeholders'] = $validated['placeholders'];

            $setting->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle event enabled status
     */
    public function toggleEnabled(string $eventId): JsonResponse
    {
        try {
            $setting = NotificationSetting::where('event_id', $eventId)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found',
                ], 404);
            }

            $setting->update(['is_enabled' => !$setting->is_enabled]);

            return response()->json([
                'success' => true,
                'message' => 'Setting toggled successfully',
                'data' => ['isEnabled' => $setting->is_enabled],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle setting: ' . $e->getMessage(),
            ], 500);
        }
    }
}
