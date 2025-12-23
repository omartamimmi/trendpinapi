<?php

namespace Modules\Notification\app\Providers\WhatsApp;

use Modules\Notification\app\Providers\AbstractNotificationChannel;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Http;

/**
 * Meta WhatsApp Business API Provider
 * Sends WhatsApp messages via Meta's Cloud API
 */
class MetaWhatsAppProvider extends AbstractNotificationChannel
{
    protected string $channelType = 'whatsapp';
    protected string $providerName = 'meta';

    private const API_BASE_URL = 'https://graph.facebook.com/v18.0';

    protected function getRequiredConfigFields(): array
    {
        return ['phone_number_id', 'access_token'];
    }

    public function send(NotificationPayload $payload): NotificationResult
    {
        $this->logAttempt($payload);

        if (!$this->isConfigured()) {
            return NotificationResult::failure('Meta WhatsApp is not configured');
        }

        try {
            $phoneNumberId = $this->getConfig('phone_number_id');
            $accessToken = $this->getConfig('access_token');

            // Format recipient number (remove + and any non-digit characters)
            $recipient = preg_replace('/[^0-9]/', '', $payload->recipientContact);

            $response = Http::withToken($accessToken)
                ->post(self::API_BASE_URL . "/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipient,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $payload->body,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $result = NotificationResult::success(
                    'WhatsApp message sent successfully',
                    $data['messages'][0]['id'] ?? null,
                    [
                        'to' => $recipient,
                        'message_status' => $data['messages'][0]['message_status'] ?? 'accepted',
                    ]
                );

                $this->logResult($result);
                return $result;
            }

            $error = $response->json();
            $errorMessage = $error['error']['message'] ?? 'Failed to send WhatsApp message';
            $errorCode = $error['error']['code'] ?? 'META_ERROR';

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
     * Send template message (required for business-initiated conversations)
     */
    public function sendTemplate(
        string $recipient,
        string $templateName,
        string $languageCode = 'en',
        array $components = []
    ): NotificationResult {
        if (!$this->isConfigured()) {
            return NotificationResult::failure('Meta WhatsApp is not configured');
        }

        try {
            $phoneNumberId = $this->getConfig('phone_number_id');
            $accessToken = $this->getConfig('access_token');

            $recipient = preg_replace('/[^0-9]/', '', $recipient);

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $recipient,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode,
                    ],
                ],
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($accessToken)
                ->post(self::API_BASE_URL . "/{$phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return NotificationResult::success(
                    'WhatsApp template message sent successfully',
                    $data['messages'][0]['id'] ?? null
                );
            }

            $error = $response->json();
            return NotificationResult::failure(
                $error['error']['message'] ?? 'Failed to send template message',
                (string) ($error['error']['code'] ?? 'META_ERROR')
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
                'Please fill in Phone Number ID and Access Token'
            );
        }

        try {
            $phoneNumberId = $this->getConfig('phone_number_id');
            $accessToken = $this->getConfig('access_token');

            // Verify credentials by fetching phone number info
            $response = Http::withToken($accessToken)
                ->get(self::API_BASE_URL . "/{$phoneNumberId}");

            if ($response->successful()) {
                $data = $response->json();

                return CredentialTestResult::success(
                    'Meta WhatsApp connection successful',
                    'Phone Number: ' . ($data['display_phone_number'] ?? $phoneNumberId),
                    [
                        'display_phone_number' => $data['display_phone_number'] ?? null,
                        'verified_name' => $data['verified_name'] ?? null,
                        'quality_rating' => $data['quality_rating'] ?? null,
                    ]
                );
            }

            $error = $response->json();
            return CredentialTestResult::failure(
                'Meta WhatsApp authentication failed',
                $error['error']['message'] ?? 'Invalid credentials',
                (string) ($error['error']['code'] ?? 'AUTH_ERROR')
            );

        } catch (\Exception $e) {
            return CredentialTestResult::failure(
                'Connection test failed',
                $e->getMessage(),
                'UNKNOWN_ERROR'
            );
        }
    }
}
