<?php

namespace Modules\Notification\app\Providers\Push;

use Modules\Notification\app\Providers\AbstractNotificationChannel;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Http;
use Google\Auth\Credentials\ServiceAccountCredentials;

/**
 * Firebase Cloud Messaging (FCM) Push Provider
 * Sends push notifications via Firebase Cloud Messaging API v1
 */
class FirebasePushProvider extends AbstractNotificationChannel
{
    protected string $channelType = 'push';
    protected string $providerName = 'firebase';

    private ?string $accessToken = null;
    private ?int $tokenExpiry = null;

    protected function getRequiredConfigFields(): array
    {
        return ['project_id', 'service_account_json'];
    }

    public function send(NotificationPayload $payload): NotificationResult
    {
        $this->logAttempt($payload);

        if (!$this->isConfigured()) {
            return NotificationResult::failure('Firebase is not configured');
        }

        try {
            $projectId = $this->getConfig('project_id');
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return NotificationResult::failure('Failed to obtain Firebase access token');
            }

            $message = [
                'message' => [
                    'token' => $payload->recipientContact, // FCM device token
                    'notification' => [
                        'title' => $payload->title ?? $payload->subject,
                        'body' => $payload->body,
                    ],
                ],
            ];

            // Add data payload if present
            if (!empty($payload->data)) {
                $message['message']['data'] = array_map('strval', $payload->data);
            }

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            if ($response->successful()) {
                $data = $response->json();

                $result = NotificationResult::success(
                    'Push notification sent successfully',
                    $data['name'] ?? null, // Message name/ID
                    ['fcm_response' => $data]
                );

                $this->logResult($result);
                return $result;
            }

            $error = $response->json();
            $errorMessage = $error['error']['message'] ?? 'Failed to send push notification';
            $errorCode = $error['error']['code'] ?? 'FCM_ERROR';

            $result = NotificationResult::failure($errorMessage, (string) $errorCode);

            $this->logResult($result);
            return $result;

        } catch (\Exception $e) {
            $result = NotificationResult::failure(
                'Unexpected error: ' . $e->getMessage(),
                'UNKNOWN_ERROR',
                $e
            );

            $this->logResult($result);
            return $result;
        }
    }

    /**
     * Send to multiple devices
     */
    public function sendBatch(array $payloads): array
    {
        if (!$this->isConfigured()) {
            return array_map(
                fn() => NotificationResult::failure('Firebase is not configured'),
                $payloads
            );
        }

        $projectId = $this->getConfig('project_id');
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return array_map(
                fn() => NotificationResult::failure('Failed to obtain Firebase access token'),
                $payloads
            );
        }

        $results = [];

        // FCM v1 doesn't have batch API, but we can use async requests
        $promises = [];

        foreach ($payloads as $index => $payload) {
            $message = [
                'message' => [
                    'token' => $payload->recipientContact,
                    'notification' => [
                        'title' => $payload->title ?? $payload->subject,
                        'body' => $payload->body,
                    ],
                ],
            ];

            if (!empty($payload->data)) {
                $message['message']['data'] = array_map('strval', $payload->data);
            }

            $promises[$index] = Http::withToken($accessToken)
                ->async()
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);
        }

        foreach ($promises as $index => $promise) {
            try {
                $response = $promise->wait();

                if ($response->successful()) {
                    $data = $response->json();
                    $results[$index] = NotificationResult::success(
                        'Push notification sent successfully',
                        $data['name'] ?? null
                    );
                } else {
                    $error = $response->json();
                    $results[$index] = NotificationResult::failure(
                        $error['error']['message'] ?? 'Failed to send push notification',
                        (string) ($error['error']['code'] ?? 'FCM_ERROR')
                    );
                }
            } catch (\Exception $e) {
                $results[$index] = NotificationResult::failure(
                    $e->getMessage(),
                    'UNKNOWN_ERROR',
                    $e
                );
            }
        }

        return $results;
    }

    /**
     * Send to topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): NotificationResult
    {
        if (!$this->isConfigured()) {
            return NotificationResult::failure('Firebase is not configured');
        }

        try {
            $projectId = $this->getConfig('project_id');
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return NotificationResult::failure('Failed to obtain Firebase access token');
            }

            $message = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ];

            if (!empty($data)) {
                $message['message']['data'] = array_map('strval', $data);
            }

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            if ($response->successful()) {
                $data = $response->json();
                return NotificationResult::success(
                    'Push notification sent to topic successfully',
                    $data['name'] ?? null
                );
            }

            $error = $response->json();
            return NotificationResult::failure(
                $error['error']['message'] ?? 'Failed to send to topic',
                (string) ($error['error']['code'] ?? 'FCM_ERROR')
            );

        } catch (\Exception $e) {
            return NotificationResult::failure(
                $e->getMessage(),
                'UNKNOWN_ERROR',
                $e
            );
        }
    }

    public function testConnection(): CredentialTestResult
    {
        if (!$this->isConfigured()) {
            return CredentialTestResult::failure(
                'Configuration incomplete',
                'Please fill in Project ID and Service Account JSON'
            );
        }

        try {
            // Validate service account JSON
            $serviceAccountJson = $this->getConfig('service_account_json');
            $serviceAccount = json_decode($serviceAccountJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return CredentialTestResult::failure(
                    'Invalid Service Account JSON',
                    'The service account JSON is not valid JSON'
                );
            }

            if (empty($serviceAccount['project_id']) || empty($serviceAccount['private_key'])) {
                return CredentialTestResult::failure(
                    'Invalid Service Account JSON',
                    'Missing required fields: project_id or private_key'
                );
            }

            // Try to get access token
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return CredentialTestResult::failure(
                    'Failed to authenticate',
                    'Could not obtain access token from Google'
                );
            }

            return CredentialTestResult::success(
                'Firebase connection successful',
                'Project: ' . $this->getConfig('project_id'),
                [
                    'project_id' => $this->getConfig('project_id'),
                    'service_account_email' => $serviceAccount['client_email'] ?? null,
                ]
            );

        } catch (\Exception $e) {
            return CredentialTestResult::failure(
                'Connection test failed',
                $e->getMessage(),
                'UNKNOWN_ERROR'
            );
        }
    }

    /**
     * Get OAuth2 access token for FCM v1 API
     */
    private function getAccessToken(): ?string
    {
        // Return cached token if still valid
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry - 60) {
            return $this->accessToken;
        }

        try {
            $serviceAccountJson = $this->getConfig('service_account_json');
            $serviceAccount = json_decode($serviceAccountJson, true);

            if (!$serviceAccount) {
                return null;
            }

            // Use Google Auth library if available
            if (class_exists(ServiceAccountCredentials::class)) {
                $credentials = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    $serviceAccount
                );

                $token = $credentials->fetchAuthToken();
                $this->accessToken = $token['access_token'] ?? null;
                $this->tokenExpiry = time() + ($token['expires_in'] ?? 3600);

                return $this->accessToken;
            }

            // Fallback: Generate JWT and exchange for access token
            return $this->getAccessTokenManually($serviceAccount);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Manually generate access token without Google Auth library
     */
    private function getAccessTokenManually(array $serviceAccount): ?string
    {
        try {
            $now = time();
            $expiry = $now + 3600;

            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            $payload = [
                'iss' => $serviceAccount['client_email'],
                'sub' => $serviceAccount['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $expiry,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            ];

            $headerEncoded = $this->base64UrlEncode(json_encode($header));
            $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

            $signatureInput = $headerEncoded . '.' . $payloadEncoded;

            $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            $jwt = $signatureInput . '.' . $this->base64UrlEncode($signature);

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? null;
                $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600);

                return $this->accessToken;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
