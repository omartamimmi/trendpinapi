<?php

namespace Modules\Geofence\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

class FcmService
{
    private ?string $projectId = null;
    private ?string $credentialsJson = null;
    private ?string $legacyServerKey = null;
    private bool $isConfigured = false;

    /**
     * Sensitive fields that are encrypted in the database
     */
    private array $sensitiveFields = [
        'server_key',
        'service_account_json',
    ];

    public function __construct()
    {
        $this->loadCredentialsFromDatabase();
    }

    /**
     * Load FCM credentials from notification_credentials table
     */
    private function loadCredentialsFromDatabase(): void
    {
        try {
            $credential = DB::table('notification_credentials')
                ->where('channel', 'push')
                ->where('provider', 'firebase')
                ->where('is_active', true)
                ->first();

            if ($credential && $credential->credentials) {
                $credentials = json_decode($credential->credentials, true);

                if (is_array($credentials)) {
                    // Decrypt sensitive fields
                    $credentials = $this->decryptSensitiveFields($credentials);

                    $this->projectId = $credentials['project_id'] ?? null;
                    $this->credentialsJson = $credentials['service_account_json'] ?? null;
                    $this->legacyServerKey = $credentials['server_key'] ?? null;
                    $this->isConfigured = !empty($this->projectId) && !empty($this->credentialsJson);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load FCM credentials from database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Decrypt sensitive fields that were encrypted by CredentialRepository
     */
    private function decryptSensitiveFields(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = Crypt::decryptString($data[$field]);
                } catch (DecryptException $e) {
                    // Field might not be encrypted, leave as is
                }
            }
        }

        return $data;
    }

    /**
     * Send a push notification
     */
    public function send(string $fcmToken, array $notification): bool
    {
        // Try FCM v1 API first
        if ($this->canUseV1Api()) {
            return $this->sendV1($fcmToken, $notification);
        }

        // Fall back to legacy API
        if ($this->legacyServerKey) {
            return $this->sendLegacy($fcmToken, $notification);
        }

        Log::error('FCM not configured: neither v1 credentials nor legacy server key found');
        return false;
    }

    /**
     * Send notification using FCM v1 API
     */
    private function sendV1(string $fcmToken, array $notification): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                Log::error('Failed to get FCM access token');
                return false;
            }

            $endpoint = sprintf(
                'https://fcm.googleapis.com/v1/projects/%s/messages:send',
                $this->projectId
            );

            $message = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                    ],
                    'data' => array_map('strval', $notification['data'] ?? []),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                        ],
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            // Add image if provided
            if (!empty($notification['image'])) {
                $message['message']['notification']['image'] = $notification['image'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $message);

            if ($response->successful()) {
                Log::info('FCM v1 notification sent successfully', [
                    'token_prefix' => substr($fcmToken, 0, 20) . '...',
                ]);
                return true;
            }

            Log::error('FCM v1 notification failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM v1 exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification using legacy FCM API
     */
    private function sendLegacy(string $fcmToken, array $notification): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->legacyServerKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'sound' => 'default',
                ],
                'data' => $notification['data'] ?? [],
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (($result['success'] ?? 0) > 0) {
                    Log::info('FCM legacy notification sent successfully', [
                        'token_prefix' => substr($fcmToken, 0, 20) . '...',
                    ]);
                    return true;
                }

                // Check for invalid token
                if (!empty($result['results'][0]['error'])) {
                    Log::warning('FCM token error', [
                        'error' => $result['results'][0]['error'],
                    ]);
                }
            }

            Log::error('FCM legacy notification failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM legacy exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send to multiple tokens (batch)
     */
    public function sendBatch(array $fcmTokens, array $notification): array
    {
        $results = [
            'success' => 0,
            'failure' => 0,
            'tokens' => [],
        ];

        foreach ($fcmTokens as $token) {
            $success = $this->send($token, $notification);
            if ($success) {
                $results['success']++;
            } else {
                $results['failure']++;
            }
            $results['tokens'][$token] = $success;
        }

        return $results;
    }

    /**
     * Check if v1 API can be used
     */
    private function canUseV1Api(): bool
    {
        if (!$this->projectId) {
            return false;
        }

        return $this->getCredentials() !== null;
    }

    /**
     * Get OAuth2 access token for FCM v1 API
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'fcm_access_token';

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $credentials = $this->getCredentials();
        if (!$credentials) {
            return null;
        }

        try {
            // Create JWT
            $now = time();
            $header = base64_encode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            $payload = base64_encode(json_encode([
                'iss' => $credentials['client_email'],
                'sub' => $credentials['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            ]));

            // Sign with private key
            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            if (!$privateKey) {
                Log::error('Invalid FCM private key');
                return null;
            }

            $signature = '';
            openssl_sign(
                $header . '.' . $payload,
                $signature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            );
            $signature = base64_encode($signature);

            // URL-safe base64
            $jwt = str_replace(['+', '/', '='], ['-', '_', ''], $header) . '.' .
                   str_replace(['+', '/', '='], ['-', '_', ''], $payload) . '.' .
                   str_replace(['+', '/', '='], ['-', '_', ''], $signature);

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache the token (with 5 minute buffer)
                Cache::put($cacheKey, $accessToken, $expiresIn - 300);

                return $accessToken;
            }

            Log::error('Failed to get FCM access token', [
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FCM access token exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get service account credentials
     */
    private function getCredentials(): ?array
    {
        if (!$this->credentialsJson) {
            return null;
        }

        $credentials = json_decode($this->credentialsJson, true);
        if ($credentials && isset($credentials['private_key'])) {
            return $credentials;
        }

        return null;
    }

    /**
     * Test FCM configuration
     */
    public function testConfiguration(): array
    {
        $result = [
            'v1_configured' => false,
            'legacy_configured' => false,
            'project_id' => $this->projectId,
            'credentials_from_database' => $this->isConfigured,
            'credentials_json_set' => !empty($this->credentialsJson),
            'legacy_key_set' => !empty($this->legacyServerKey),
        ];

        $result['v1_configured'] = $this->canUseV1Api();
        $result['legacy_configured'] = !empty($this->legacyServerKey);
        $result['recommended_api'] = $result['v1_configured'] ? 'v1' : ($result['legacy_configured'] ? 'legacy' : 'none');

        // Validate service account JSON structure
        if ($this->credentialsJson) {
            $credentials = json_decode($this->credentialsJson, true);
            $result['service_account_valid'] = $credentials &&
                isset($credentials['private_key']) &&
                isset($credentials['client_email']) &&
                isset($credentials['project_id']);

            if (!$result['service_account_valid']) {
                $result['service_account_error'] = 'Invalid service account JSON structure. Required fields: private_key, client_email, project_id';
            }
        }

        return $result;
    }

    /**
     * Validate FCM token format (dry run without sending)
     */
    public function validateToken(string $fcmToken): array
    {
        $result = [
            'valid_format' => false,
            'token_length' => strlen($fcmToken),
        ];

        // FCM tokens are typically 150-180 characters
        if (strlen($fcmToken) >= 100 && strlen($fcmToken) <= 300) {
            $result['valid_format'] = true;
        }

        return $result;
    }

    /**
     * Dry run test - validates config without sending
     */
    public function dryRunTest(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => [],
        ];

        $config = $this->testConfiguration();
        $result['details'] = $config;

        if (!$config['v1_configured'] && !$config['legacy_configured']) {
            $result['message'] = 'FCM is not configured. Please configure push notifications in Admin > Notification Credentials.';
            return $result;
        }

        if ($config['v1_configured']) {
            // Try to get an access token to validate credentials
            $accessToken = $this->getAccessToken();
            if ($accessToken) {
                $result['success'] = true;
                $result['message'] = 'FCM v1 API is configured and OAuth token was successfully generated.';
                $result['details']['oauth_test'] = 'passed';
            } else {
                $result['message'] = 'FCM v1 API credentials are set but OAuth token generation failed. Check service account JSON.';
                $result['details']['oauth_test'] = 'failed';
            }
        } elseif ($config['legacy_configured']) {
            $result['success'] = true;
            $result['message'] = 'FCM Legacy API is configured (deprecated but functional).';
        }

        return $result;
    }
}
