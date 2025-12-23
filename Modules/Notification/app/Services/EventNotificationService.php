<?php

namespace Modules\Notification\app\Services;

use App\Models\User;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\Models\NotificationSetting;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending event-based notifications
 * Uses templates from database and credential-based providers
 */
class EventNotificationService
{
    private CredentialService $credentialService;

    public function __construct(?CredentialService $credentialService = null)
    {
        $this->credentialService = $credentialService ?? new CredentialService();
    }

    /**
     * Send notification for an event
     *
     * @param string $eventId The event identifier (e.g., 'new_customer', 'retailer_approved')
     * @param string $recipientType The recipient type ('admin', 'retailer', 'customer')
     * @param User|string $recipient The recipient user or user ID
     * @param array $placeholders Key-value pairs for template placeholders
     * @return array Results for each channel attempted
     */
    public function sendEventNotification(
        string $eventId,
        string $recipientType,
        User|string $recipient,
        array $placeholders = []
    ): array {
        // Get the notification setting from database
        $setting = NotificationSetting::where('event_id', $eventId)->first();

        if (!$setting) {
            Log::warning("No notification setting found for event: {$eventId}");
            return ['success' => false, 'error' => 'Event not configured'];
        }

        // Check if the event is enabled
        if (!$setting->is_enabled) {
            Log::info("Notification event {$eventId} is disabled");
            return ['success' => false, 'error' => 'Event is disabled'];
        }

        // Check if this recipient type should receive this notification
        if (!in_array($recipientType, $setting->recipients ?? [])) {
            Log::info("Recipient type {$recipientType} is not configured for event {$eventId}");
            return ['success' => false, 'error' => 'Recipient type not configured'];
        }

        // Get the recipient user
        $user = $recipient instanceof User ? $recipient : User::find($recipient);

        if (!$user) {
            return ['success' => false, 'error' => 'Recipient not found'];
        }

        // Add default placeholders
        $placeholders = $this->addDefaultPlaceholders($placeholders, $user);

        // Send to each enabled channel
        $results = [];
        $channels = $setting->channels ?? [];

        foreach ($channels as $channel => $enabled) {
            if (!$enabled) {
                continue;
            }

            $result = $this->sendToChannel(
                $channel,
                $setting,
                $recipientType,
                $user,
                $placeholders
            );

            $results[$channel] = $result;
        }

        // Check if at least one channel succeeded
        $anySuccess = collect($results)->contains(fn($r) => $r->success);

        return [
            'success' => $anySuccess,
            'channels' => array_map(fn($r) => $r->toArray(), $results),
        ];
    }

    /**
     * Send to a specific channel
     */
    private function sendToChannel(
        string $channel,
        NotificationSetting $setting,
        string $recipientType,
        User $user,
        array $placeholders
    ): NotificationResult {
        // Get the channel provider
        $provider = $this->credentialService->getChannelProvider($channel);

        if (!$provider) {
            return NotificationResult::failure(
                "Channel '{$channel}' is not configured or inactive",
                'CHANNEL_NOT_CONFIGURED'
            );
        }

        // Get the template for this recipient type and channel
        $template = $this->getTemplate($setting, $recipientType, $channel);

        if (!$template) {
            return NotificationResult::failure(
                "No template configured for {$recipientType} on {$channel}",
                'TEMPLATE_NOT_FOUND'
            );
        }

        // Get contact info for this channel
        $contact = $this->getRecipientContact($user, $channel);

        if (!$contact) {
            return NotificationResult::failure(
                "Recipient has no {$channel} contact info",
                'MISSING_CONTACT'
            );
        }

        // Build the payload
        $payload = new NotificationPayload(
            recipientId: (string) $user->id,
            recipientType: $recipientType,
            recipientContact: $contact,
            subject: $template['subject'] ?? '',
            body: $template['body'] ?? '',
            title: $template['title'] ?? null,
            data: ['event_id' => $setting->event_id],
            templateId: $setting->event_id,
            placeholders: $placeholders,
        );

        // Replace placeholders in the payload
        $payload = $payload->withReplacedPlaceholders($placeholders);

        // Send the notification
        try {
            $result = $provider->send($payload);

            Log::info('Event notification sent', [
                'event_id' => $setting->event_id,
                'channel' => $channel,
                'recipient_type' => $recipientType,
                'recipient_id' => $user->id,
                'success' => $result->success,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Event notification failed', [
                'event_id' => $setting->event_id,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return NotificationResult::failure(
                'Failed to send notification: ' . $e->getMessage(),
                'SEND_ERROR',
                $e
            );
        }
    }

    /**
     * Get template from settings for recipient type and channel
     */
    private function getTemplate(NotificationSetting $setting, string $recipientType, string $channel): ?array
    {
        $templates = $setting->templates ?? [];

        // Try to get template for recipient type and channel
        if (isset($templates[$recipientType][$channel])) {
            return $templates[$recipientType][$channel];
        }

        // Fallback: try to get template for recipient type (any channel)
        if (isset($templates[$recipientType])) {
            $recipientTemplates = $templates[$recipientType];

            // If it's directly the template format (has 'subject' or 'body')
            if (isset($recipientTemplates['subject']) || isset($recipientTemplates['body'])) {
                return $recipientTemplates;
            }

            // Get the first available channel template
            return array_values($recipientTemplates)[0] ?? null;
        }

        return null;
    }

    /**
     * Get recipient contact based on channel
     */
    private function getRecipientContact(User $user, string $channel): ?string
    {
        return match ($channel) {
            'email', 'smtp' => $user->email,
            'sms', 'whatsapp' => $user->phone,
            'push' => $user->fcm_token ?? null,
            default => null,
        };
    }

    /**
     * Add default placeholder values
     */
    private function addDefaultPlaceholders(array $placeholders, User $user): array
    {
        $defaults = [
            'app_name' => config('app.name', 'TrendPin'),
            'customer_name' => $user->name ?? 'Customer',
            'retailer_name' => $user->name ?? 'Retailer',
            'user_name' => $user->name ?? 'User',
            'user_email' => $user->email ?? '',
            'customer_email' => $user->email ?? '',
            'retailer_email' => $user->email ?? '',
            'registration_date' => now()->format('Y-m-d H:i'),
            'current_date' => now()->format('Y-m-d'),
            'current_time' => now()->format('H:i'),
        ];

        // Merge defaults with provided placeholders (provided take precedence)
        return array_merge($defaults, $placeholders);
    }

    /**
     * Send notification to all admins for an event
     */
    public function notifyAdmins(string $eventId, array $placeholders = []): array
    {
        $admins = User::role('admin')->get();
        $results = [];

        foreach ($admins as $admin) {
            $results[$admin->id] = $this->sendEventNotification(
                $eventId,
                'admin',
                $admin,
                $placeholders
            );
        }

        return $results;
    }

    /**
     * Quick helper methods for common events
     */
    public function sendNewCustomerNotification(User $customer): array
    {
        // Notify the customer
        $customerResult = $this->sendEventNotification(
            'new_customer',
            'customer',
            $customer,
            [
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
            ]
        );

        // Notify admins
        $adminResults = $this->notifyAdmins('new_customer', [
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
        ]);

        return [
            'customer' => $customerResult,
            'admins' => $adminResults,
        ];
    }

    public function sendNewRetailerNotification(User $retailer, array $businessInfo = []): array
    {
        $placeholders = array_merge([
            'retailer_name' => $retailer->name,
            'retailer_email' => $retailer->email,
        ], $businessInfo);

        // Notify the retailer
        $retailerResult = $this->sendEventNotification(
            'new_retailer',
            'retailer',
            $retailer,
            $placeholders
        );

        // Notify admins
        $adminResults = $this->notifyAdmins('new_retailer', $placeholders);

        return [
            'retailer' => $retailerResult,
            'admins' => $adminResults,
        ];
    }

    public function sendRetailerApprovedNotification(User $retailer): array
    {
        return $this->sendEventNotification(
            'retailer_approved',
            'retailer',
            $retailer,
            [
                'retailer_name' => $retailer->name,
            ]
        );
    }

    public function sendRetailerRejectedNotification(User $retailer, string $reason = ''): array
    {
        return $this->sendEventNotification(
            'retailer_rejected',
            'retailer',
            $retailer,
            [
                'retailer_name' => $retailer->name,
                'admin_message' => $reason,
            ]
        );
    }

    public function sendPhoneVerificationNotification(User $user, string $recipientType, string $otpCode, int $expiryMinutes = 5): array
    {
        return $this->sendEventNotification(
            'phone_verification',
            $recipientType,
            $user,
            [
                'otp_code' => $otpCode,
                'expiry_minutes' => (string) $expiryMinutes,
            ]
        );
    }
}
