<?php

namespace Modules\Notification\app\Services;

interface NotificationProviderInterface
{
    /**
     * Send notification to recipient
     *
     * @param string $recipient (FCM token, phone number, email)
     * @param array $message ['title' => '', 'body' => '', 'data' => []]
     * @return array ['success' => bool, 'message_id' => string, 'response' => mixed]
     */
    public function send(string $recipient, array $message): array;

    /**
     * Send batch notifications
     *
     * @param array $recipients
     * @param array $message
     * @return array ['success_count' => int, 'failure_count' => int, 'results' => array]
     */
    public function sendBatch(array $recipients, array $message): array;

    /**
     * Validate provider credentials
     *
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateCredentials(): array;

    /**
     * Get delivery status
     *
     * @param string $messageId
     * @return array ['status' => string, 'delivered_at' => ?string]
     */
    public function getDeliveryStatus(string $messageId): array;
}
