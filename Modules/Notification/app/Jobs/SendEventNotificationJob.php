<?php

namespace Modules\Notification\app\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Notification\app\Services\EventNotificationService;

class SendEventNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        public string $eventId,
        public string $recipientType,
        public int $userId,
        public array $placeholders = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("SendEventNotificationJob: User not found", [
                'user_id' => $this->userId,
                'event_id' => $this->eventId,
            ]);
            return;
        }

        try {
            $service = new EventNotificationService();
            $result = $service->sendEventNotification(
                $this->eventId,
                $this->recipientType,
                $user,
                $this->placeholders
            );

            Log::info("SendEventNotificationJob: Completed", [
                'event_id' => $this->eventId,
                'user_id' => $this->userId,
                'success' => $result['success'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error("SendEventNotificationJob: Failed", [
                'event_id' => $this->eventId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendEventNotificationJob: Permanently failed", [
            'event_id' => $this->eventId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
