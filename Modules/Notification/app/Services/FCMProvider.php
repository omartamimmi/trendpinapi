<?php

namespace Modules\Notification\app\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMProvider implements NotificationProviderInterface
{
    protected $serverKey;
    protected $senderId;

    public function __construct(array $credentials)
    {
        $this->serverKey = $credentials['server_key'] ?? null;
        $this->senderId = $credentials['sender_id'] ?? null;
    }

    public function send(string $recipient, array $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $recipient,
                'notification' => [
                    'title' => $message['title'] ?? '',
                    'body' => $message['body'] ?? '',
                    'image' => $message['image_url'] ?? null,
                    'sound' => 'default',
                ],
                'data' => $message['data'] ?? [],
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'message_id' => $result['results'][0]['message_id'] ?? null,
                    'response' => $result,
                ];
            }

            return [
                'success' => false,
                'message_id' => null,
                'response' => $response->json(),
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('FCM send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message_id' => null,
                'response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendBatch(array $recipients, array $message): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        // FCM supports batch sending to max 1000 tokens
        $chunks = array_chunk($recipients, 1000);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $message['title'] ?? '',
                        'body' => $message['body'] ?? '',
                        'image' => $message['image_url'] ?? null,
                        'sound' => 'default',
                    ],
                    'data' => $message['data'] ?? [],
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $successCount += $result['success'] ?? 0;
                    $failureCount += $result['failure'] ?? 0;
                    $results[] = $result;
                } else {
                    $failureCount += count($chunk);
                    $results[] = ['error' => $response->body()];
                }
            } catch (\Exception $e) {
                $failureCount += count($chunk);
                $results[] = ['error' => $e->getMessage()];
                Log::error('FCM batch send error: ' . $e->getMessage());
            }
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results,
        ];
    }

    public function validateCredentials(): array
    {
        if (empty($this->serverKey)) {
            return ['valid' => false, 'message' => 'Server key is required'];
        }

        // Try a test send to validate
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => ['test_token'],
                'dry_run' => true,
                'notification' => [
                    'title' => 'Test',
                    'body' => 'Test',
                ],
            ]);

            // Even with invalid token, if credentials are valid, FCM returns 200
            if ($response->status() === 200) {
                return ['valid' => true, 'message' => 'Credentials are valid'];
            }

            if ($response->status() === 401) {
                return ['valid' => false, 'message' => 'Invalid server key'];
            }

            return ['valid' => false, 'message' => 'Validation failed: ' . $response->body()];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    public function getDeliveryStatus(string $messageId): array
    {
        // FCM doesn't provide a status check API
        // Status is typically received via delivery receipts
        return [
            'status' => 'unknown',
            'delivered_at' => null,
            'message' => 'FCM does not support delivery status lookup',
        ];
    }
}
