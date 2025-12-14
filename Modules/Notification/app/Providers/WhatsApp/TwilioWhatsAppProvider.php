<?php

namespace Modules\Notification\app\Providers\WhatsApp;

use Modules\Notification\app\Providers\AbstractNotificationChannel;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Http;

/**
 * Twilio WhatsApp Provider
 * Sends WhatsApp messages via Twilio API
 */
class TwilioWhatsAppProvider extends AbstractNotificationChannel
{
    protected string $channelType = 'whatsapp';
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
            return NotificationResult::failure('Twilio WhatsApp is not configured');
        }

        try {
            $accountSid = $this->getConfig('account_sid');
            $authToken = $this->getConfig('auth_token');
            $fromNumber = $this->getConfig('from_number');

            // Ensure WhatsApp format
            $to = $this->formatWhatsAppNumber($payload->recipientContact);
            $from = $this->formatWhatsAppNumber($fromNumber);

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post(self::API_BASE_URL . "/Accounts/{$accountSid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $payload->body,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $result = NotificationResult::success(
                    'WhatsApp message sent successfully',
                    $data['sid'] ?? null,
                    [
                        'status' => $data['status'] ?? 'unknown',
                        'to' => $data['to'] ?? $to,
                    ]
                );

                $this->logResult($result);
                return $result;
            }

            $error = $response->json();
            $result = NotificationResult::failure(
                $error['message'] ?? 'Failed to send WhatsApp message',
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
                'Please fill in Account SID, Auth Token, and WhatsApp Number'
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
                    'Twilio WhatsApp connection successful',
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
     * Format phone number for WhatsApp
     */
    private function formatWhatsAppNumber(string $number): string
    {
        // Remove any existing whatsapp: prefix
        $number = str_replace('whatsapp:', '', $number);

        // Ensure it starts with +
        if (!str_starts_with($number, '+')) {
            $number = '+' . $number;
        }

        return 'whatsapp:' . $number;
    }
}
