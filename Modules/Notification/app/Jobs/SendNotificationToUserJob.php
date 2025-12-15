<?php

namespace Modules\Notification\app\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\Models\NotificationDelivery;
use Modules\Notification\app\Models\NotificationMessage;
use Modules\Notification\app\Services\NotificationService;

class SendNotificationToUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public function __construct(
        public int $messageId,
        public int $userId,
        public string $channel
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $message = NotificationMessage::find($this->messageId);
        $user = User::find($this->userId);

        if (!$message || !$user) {
            Log::warning("SendNotificationToUserJob: Message or user not found", [
                'message_id' => $this->messageId,
                'user_id' => $this->userId,
            ]);
            return;
        }

        try {
            $service = new NotificationService();
            $result = $service->sendToUserChannel($message, $user, $this->channel);

            Log::info("SendNotificationToUserJob: Completed", [
                'message_id' => $this->messageId,
                'user_id' => $this->userId,
                'channel' => $this->channel,
                'success' => $result['success'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error("SendNotificationToUserJob: Failed", [
                'message_id' => $this->messageId,
                'user_id' => $this->userId,
                'channel' => $this->channel,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Update delivery status to failed
        NotificationDelivery::where('message_id', $this->messageId)
            ->where('user_id', $this->userId)
            ->update([
                'status' => 'failed',
                'failed_reason' => $exception->getMessage(),
            ]);

        Log::error("SendNotificationToUserJob: Permanently failed", [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'channel' => $this->channel,
            'error' => $exception->getMessage(),
        ]);
    }
}
