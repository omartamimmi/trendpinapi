<?php

namespace Modules\Notification\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Notification\app\Models\NotificationMessage;
use Modules\Notification\app\Services\NotificationService;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        public int $messageId
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $message = NotificationMessage::find($this->messageId);

        if (!$message) {
            Log::warning("SendBulkNotificationJob: Message not found", [
                'message_id' => $this->messageId,
            ]);
            return;
        }

        try {
            $message->update(['status' => 'processing']);

            $service = new NotificationService();
            $result = $service->sendNotification($message);

            $message->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("SendBulkNotificationJob: Completed", [
                'message_id' => $this->messageId,
                'success' => $result['success'] ?? false,
            ]);
        } catch (\Exception $e) {
            $message->update(['status' => 'failed']);

            Log::error("SendBulkNotificationJob: Failed", [
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $message = NotificationMessage::find($this->messageId);
        if ($message) {
            $message->update(['status' => 'failed']);
        }

        Log::error("SendBulkNotificationJob: Permanently failed", [
            'message_id' => $this->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
