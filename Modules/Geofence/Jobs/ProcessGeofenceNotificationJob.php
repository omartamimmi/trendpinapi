<?php

namespace Modules\Geofence\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Geofence\DTO\RadarEventDTO;
use Modules\Geofence\Services\Contracts\GeofenceNotificationServiceInterface;
use Illuminate\Support\Facades\Log;

class ProcessGeofenceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public RadarEventDTO $event
    ) {
        $this->onQueue('geofence');
    }

    /**
     * Execute the job.
     */
    public function handle(GeofenceNotificationServiceInterface $geofenceNotificationService): void
    {
        Log::info('Processing geofence notification job', [
            'event_id' => $this->event->eventId,
            'event_type' => $this->event->type,
            'user_id' => $this->event->userId,
        ]);

        try {
            $result = $geofenceNotificationService->processGeofenceEvent($this->event);

            Log::info('Geofence notification job completed', [
                'event_id' => $this->event->eventId,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Geofence notification job failed', [
                'event_id' => $this->event->eventId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Geofence notification job permanently failed', [
            'event_id' => $this->event->eventId,
            'error' => $exception->getMessage(),
        ]);
    }
}
