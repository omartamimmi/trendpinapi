<?php

namespace Modules\Notification\app\Providers\Sms;

use Modules\Notification\app\Providers\AbstractNotificationChannel;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Http;

/**
 * Twilio SMS Provider
 * Sends SMS messages via Twilio API
 */
class TwilioSmsProvider extends AbstractNotificationChannel
{
    protected string $channelType = 'sms';
    protected string $providerName = 'twilio';

    private const API_BASE_URL = 'https://api.twilio.com/2010-04-01';

    protected function getRequiredConfigFields(): array
    {
        return ['account_sid', 'auth_token', 'from_number'];
    }

    public function send(NotificationPayload $payload): NotificationResult
    {
        $this->logAttempt($payload);

        if (!$this->isConfigured()) {
            return NotificationResult::failure('Twilio SMS is not configured');
        }

        try {
            $accountSid = $this->getConfig('account_sid');
            $authToken = $this->getConfig('auth_token');
            $fromNumber = $this->getConfig('from_number');

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post(self::API_BASE_URL . "/Accounts/{$accountSid}/Messages.json", [
                    'To' => $payload->recipientContact,
                    'From' => $fromNumber,
                    'Body' => $payload->body,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $result = NotificationResult::success(
                    'SMS sent successfully',
                    $data['sid'] ?? null,
                    [
                        'status' => $data['status'] ?? 'unknown',
                        'to' => $data['to'] ?? $payload->recipientContact,
                    ]
                );

                $this->logResult($result);
                return $result;
            }

            $error = $response->json();
            $result = NotificationResult::failure(
                $error['message'] ?? 'Failed to send SMS',
                $error['code'] ?? 'TWILIO_ERROR'
            );

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

    public function testConnection(): CredentialTestResult
    {
        if (!$this->isConfigured()) {
            return CredentialTestResult::failure(
                'Configuration incomplete',
                'Please fill in Account SID, Auth Token, and From Number'
            );
        }

        try {
            $accountSid = $this->getConfig('account_sid');
            $authToken = $this->getConfig('auth_token');

            // Verify credentials by fetching account info
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->get(self::API_BASE_URL . "/Accounts/{$accountSid}.json");

            if ($response->successful()) {
                $data = $response->json();

                return CredentialTestResult::success(
                    'Twilio connection successful',
                    'Account: ' . ($data['friendly_name'] ?? $accountSid),
                    [
                        'account_name' => $data['friendly_name'] ?? null,
                        'status' => $data['status'] ?? 'unknown',
                    ]
                );
            }

            $error = $response->json();
            return CredentialTestResult::failure(
                'Twilio authentication failed',
                $error['message'] ?? 'Invalid credentials',
                (string) ($error['code'] ?? 'AUTH_ERROR')
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
     * Send batch SMS (optimized for Twilio)
     */
    public function sendBatch(array $payloads): array
    {
        // Twilio doesn't have a native batch API, but we can use async requests
        $results = [];
        $accountSid = $this->getConfig('account_sid');
        $authToken = $this->getConfig('auth_token');
        $fromNumber = $this->getConfig('from_number');

        $promises = [];

        foreach ($payloads as $index => $payload) {
            $promises[$index] = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->async()
                ->post(self::API_BASE_URL . "/Accounts/{$accountSid}/Messages.json", [
                    'To' => $payload->recipientContact,
                    'From' => $fromNumber,
                    'Body' => $payload->body,
                ]);
        }

        foreach ($promises as $index => $promise) {
            try {
                $response = $promise->wait();

                if ($response->successful()) {
                    $data = $response->json();
                    $results[$index] = NotificationResult::success(
                        'SMS sent successfully',
                        $data['sid'] ?? null
                    );
                } else {
                    $error = $response->json();
                    $results[$index] = NotificationResult::failure(
                        $error['message'] ?? 'Failed to send SMS',
                        $error['code'] ?? 'TWILIO_ERROR'
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
}
