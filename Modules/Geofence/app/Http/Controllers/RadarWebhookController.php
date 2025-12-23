<?php

namespace Modules\Geofence\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Geofence\app\Http\Requests\RadarWebhookRequest;
use Modules\Geofence\Services\Contracts\GeofenceNotificationServiceInterface;
use Modules\Geofence\DTO\RadarEventDTO;
use Modules\Geofence\Jobs\ProcessGeofenceNotificationJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RadarWebhookController extends Controller
{
    public function __construct(
        private GeofenceNotificationServiceInterface $geofenceNotificationService
    ) {}

    /**
     * Handle incoming Radar.io webhook
     */
    public function handle(RadarWebhookRequest $request): JsonResponse
    {
        $events = $request->getEvents();
        $results = [];

        Log::info('Radar webhook received', [
            'event_count' => count($events),
        ]);

        foreach ($events as $eventData) {
            try {
                $event = RadarEventDTO::fromWebhook($eventData);

                // Skip non-geofence events
                if (!$event->isGeofenceEntry() && !$event->isGeofenceExit() && !$event->isGeofenceDwell()) {
                    $results[] = [
                        'event_id' => $event->eventId,
                        'status' => 'skipped',
                        'reason' => 'not_a_geofence_event',
                    ];
                    continue;
                }

                // Process asynchronously for better performance
                if (config('geofence.async_processing', true)) {
                    ProcessGeofenceNotificationJob::dispatch($event);
                    $results[] = [
                        'event_id' => $event->eventId,
                        'status' => 'queued',
                    ];
                } else {
                    // Process synchronously
                    $result = $this->geofenceNotificationService->processGeofenceEvent($event);
                    $results[] = [
                        'event_id' => $event->eventId,
                        'status' => 'processed',
                        'result' => $result,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error processing radar webhook event', [
                    'event_data' => $eventData,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'event_id' => $eventData['_id'] ?? 'unknown',
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'processed' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Verify webhook endpoint (Radar.io may ping this)
     */
    public function verify(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Webhook endpoint is active',
        ]);
    }
}
