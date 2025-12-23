<?php

namespace Modules\Notification\app\Services;

use App\Models\User;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\Factories\NotificationChannelFactory;
use Modules\Notification\app\Models\NotificationSetting;
use Illuminate\Support\Facades\Log;

/**
 * Service for testing notification sending
 */
class NotificationTestService
{
    private CredentialService $credentialService;

    public function __construct(?CredentialService $credentialService = null)
    {
        $this->credentialService = $credentialService ?? new CredentialService();
    }

    /**
     * Send a test notification
     */
    public function sendTest(
        string $channel,
        string $recipientType,
        string $recipientId,
        string $eventId,
        array $placeholders = []
    ): NotificationResult {
        // Get the channel provider
        $provider = $this->credentialService->getChannelProvider($channel);

        if (!$provider) {
            return NotificationResult::failure(
                "Channel '{$channel}' is not configured or inactive",
                'CHANNEL_NOT_CONFIGURED'
            );
        }

        // Get recipient info
        $recipient = $this->getRecipient($recipientType, $recipientId);

        if (!$recipient) {
            return NotificationResult::failure(
                "Recipient not found",
                'RECIPIENT_NOT_FOUND'
            );
        }

        // Get contact info based on channel
        $contact = $this->getRecipientContact($recipient, $channel);

        if (!$contact) {
            return NotificationResult::failure(
                "Recipient has no {$channel} contact info",
                'MISSING_CONTACT'
            );
        }

        // Build the payload
        $template = $this->getTestTemplate($eventId, $channel, $recipientType);
        $payload = new NotificationPayload(
            recipientId: $recipientId,
            recipientType: $recipientType,
            recipientContact: $contact,
            subject: $template['subject'],
            body: $template['body'],
            title: $template['title'] ?? null,
            data: ['test' => true, 'event_id' => $eventId],
            templateId: $eventId,
            placeholders: $placeholders,
        );

        // Replace placeholders
        $payload = $payload->withReplacedPlaceholders($placeholders);

        // Send the notification
        try {
            $result = $provider->send($payload);

            Log::info('Test notification sent', [
                'channel' => $channel,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'event_id' => $eventId,
                'success' => $result->success,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Test notification failed', [
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return NotificationResult::failure(
                'Failed to send test notification: ' . $e->getMessage(),
                'SEND_ERROR',
                $e
            );
        }
    }

    /**
     * Get recipients by type for selection
     */
    public function getRecipientsByType(string $type, int $limit = 50): array
    {
        $query = User::query();

        switch ($type) {
            case 'admin':
                $query->role('admin');
                break;
            case 'retailer':
                $query->role('retailer');
                break;
            case 'customer':
                $query->role('customer');
                break;
            default:
                return [];
        }

        return $query->limit($limit)->get()->map(function ($user) {
            return [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ];
        })->toArray();
    }

    /**
     * Get recipient by type and ID
     */
    private function getRecipient(string $type, string $id): ?User
    {
        $user = User::find($id);

        if (!$user) {
            return null;
        }

        // Optionally verify role matches type
        // For now, we'll trust the caller

        return $user;
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
     * Get test template for an event
     */
    private function getTestTemplate(string $eventId, string $channel, string $recipientType): array
    {
        // Default test templates
        $templates = [
            'phone_verification' => [
                'admin' => [
                    'subject' => 'Verification Code - {{app_name}}',
                    'body' => "Your verification code is: {{otp_code}}\n\nThis code expires in {{expiry_minutes}} minutes.",
                    'title' => 'Verification Code',
                ],
                'retailer' => [
                    'subject' => 'Verification Code - {{app_name}}',
                    'body' => "Your verification code is: {{otp_code}}\n\nThis code expires in {{expiry_minutes}} minutes.\n\nDo not share this code with anyone.",
                    'title' => 'Verification Code',
                ],
                'customer' => [
                    'subject' => 'Verification Code - {{app_name}}',
                    'body' => "Your {{app_name}} verification code is: {{otp_code}}\n\nExpires in {{expiry_minutes}} minutes.",
                    'title' => 'Verification Code',
                ],
            ],
            'new_customer' => [
                'admin' => [
                    'subject' => 'New Customer Registration - {{customer_name}}',
                    'body' => "A new customer has registered.\n\nCustomer: {{customer_name}}\nEmail: {{customer_email}}",
                    'title' => 'New Customer',
                ],
                'customer' => [
                    'subject' => 'Welcome to {{app_name}}!',
                    'body' => "Hi {{customer_name}},\n\nWelcome to {{app_name}}! We're excited to have you.",
                    'title' => 'Welcome!',
                ],
            ],
            'new_retailer' => [
                'admin' => [
                    'subject' => 'New Retailer Application - {{retailer_name}}',
                    'body' => "A new retailer has applied.\n\nRetailer: {{retailer_name}}\nBusiness: {{business_name}}",
                    'title' => 'New Application',
                ],
                'retailer' => [
                    'subject' => 'Application Received - {{app_name}}',
                    'body' => "Hi {{retailer_name}},\n\nWe have received your application and will review it shortly.",
                    'title' => 'Application Received',
                ],
            ],
            'retailer_approved' => [
                'retailer' => [
                    'subject' => 'Congratulations! Your Account is Approved',
                    'body' => "Hi {{retailer_name}},\n\nYour retailer application has been approved. You can now start adding your offers.",
                    'title' => 'Account Approved!',
                ],
            ],
            'retailer_rejected' => [
                'retailer' => [
                    'subject' => 'Application Update - {{app_name}}',
                    'body' => "Hi {{retailer_name}},\n\nWe were unable to approve your application.\n\nReason: {{admin_message}}",
                    'title' => 'Application Update',
                ],
            ],
            'retailer_changes_requested' => [
                'retailer' => [
                    'subject' => 'Action Required: Changes Needed',
                    'body' => "Hi {{retailer_name}},\n\nChanges are needed for your application:\n\n{{admin_message}}",
                    'title' => 'Changes Requested',
                ],
            ],
            'subscription_success' => [
                'admin' => [
                    'subject' => 'New Subscription - {{retailer_name}}',
                    'body' => "New subscription activated.\n\nRetailer: {{retailer_name}}\nPlan: {{plan_name}}",
                    'title' => 'New Subscription',
                ],
                'retailer' => [
                    'subject' => 'Subscription Activated - {{plan_name}}',
                    'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} subscription is now active until {{expiry_date}}.",
                    'title' => 'Subscription Active!',
                ],
            ],
            'subscription_cancelled' => [
                'admin' => [
                    'subject' => 'Subscription Cancelled - {{retailer_name}}',
                    'body' => "Subscription cancelled.\n\nRetailer: {{retailer_name}}\nPlan: {{plan_name}}",
                    'title' => 'Subscription Cancelled',
                ],
                'retailer' => [
                    'subject' => 'Subscription Cancelled',
                    'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} subscription has been cancelled. Access ends on {{end_date}}.",
                    'title' => 'Subscription Cancelled',
                ],
            ],
            'subscription_expiring' => [
                'retailer' => [
                    'subject' => 'Subscription Expiring in {{days_left}} Days',
                    'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} expires on {{expiry_date}}. Renew now!",
                    'title' => 'Expiring Soon',
                ],
            ],
            'branch_published' => [
                'admin' => [
                    'subject' => 'Branch Published - {{branch_name}}',
                    'body' => "New branch published.\n\nRetailer: {{retailer_name}}\nBranch: {{branch_name}}",
                    'title' => 'Branch Published',
                ],
                'retailer' => [
                    'subject' => 'Your Branch is Now Live!',
                    'body' => "Hi {{retailer_name}},\n\nYour branch \"{{branch_name}}\" is now visible to customers.",
                    'title' => 'Branch Live!',
                ],
            ],
            'nearby_shop' => [
                'customer' => [
                    'subject' => '{{shop_name}} is nearby!',
                    'body' => "Hi {{customer_name}},\n\n{{shop_name}} is {{distance}} away with {{offer_count}} offers!",
                    'title' => '{{shop_name}} Nearby',
                ],
            ],
        ];

        // Get template for this event and recipient type
        if (isset($templates[$eventId][$recipientType])) {
            return $templates[$eventId][$recipientType];
        }

        // Default fallback template
        return [
            'subject' => 'Test Notification - ' . $eventId,
            'body' => "This is a test notification for event: {$eventId}\n\nRecipient type: {$recipientType}",
            'title' => 'Test Notification',
        ];
    }

    /**
     * Get available events for testing
     */
    public function getAvailableEvents(): array
    {
        return [
            ['id' => 'phone_verification', 'name' => 'Phone Verification OTP', 'category' => 'Authentication'],
            ['id' => 'new_customer', 'name' => 'New Customer', 'category' => 'Customer'],
            ['id' => 'nearby_shop', 'name' => 'Nearby Shop', 'category' => 'Customer'],
            ['id' => 'new_retailer', 'name' => 'New Retailer', 'category' => 'Retailer'],
            ['id' => 'retailer_approved', 'name' => 'Retailer Approved', 'category' => 'Retailer'],
            ['id' => 'retailer_rejected', 'name' => 'Retailer Rejected', 'category' => 'Retailer'],
            ['id' => 'retailer_changes_requested', 'name' => 'Request Changes', 'category' => 'Retailer'],
            ['id' => 'subscription_success', 'name' => 'Success Subscription', 'category' => 'Subscription'],
            ['id' => 'subscription_cancelled', 'name' => 'Cancel Subscription', 'category' => 'Subscription'],
            ['id' => 'subscription_expiring', 'name' => 'Subscription Expiring', 'category' => 'Subscription'],
            ['id' => 'branch_published', 'name' => 'Published Branch', 'category' => 'Branch'],
        ];
    }

    /**
     * Get placeholders for an event
     */
    public function getEventPlaceholders(string $eventId): array
    {
        $placeholders = [
            'phone_verification' => ['otp_code', 'expiry_minutes', 'app_name'],
            'new_customer' => ['customer_name', 'customer_email', 'app_name', 'registration_date'],
            'nearby_shop' => ['customer_name', 'shop_name', 'distance', 'offer_count'],
            'new_retailer' => ['retailer_name', 'retailer_email', 'business_name', 'app_name', 'submission_date'],
            'retailer_approved' => ['retailer_name', 'app_name'],
            'retailer_rejected' => ['retailer_name', 'app_name', 'admin_message'],
            'retailer_changes_requested' => ['retailer_name', 'app_name', 'admin_message'],
            'subscription_success' => ['retailer_name', 'plan_name', 'amount', 'expiry_date', 'app_name'],
            'subscription_cancelled' => ['retailer_name', 'plan_name', 'end_date', 'app_name'],
            'subscription_expiring' => ['retailer_name', 'plan_name', 'expiry_date', 'days_left', 'app_name'],
            'branch_published' => ['retailer_name', 'branch_name', 'branch_address', 'app_name'],
        ];

        return $placeholders[$eventId] ?? [];
    }

    /**
     * Get default placeholder values for testing
     */
    public function getDefaultPlaceholderValues(string $eventId): array
    {
        $defaults = [
            'otp_code' => '123456',
            'expiry_minutes' => '5',
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@test.com',
            'retailer_name' => 'Test Retailer',
            'retailer_email' => 'retailer@test.com',
            'business_name' => 'Test Business',
            'app_name' => config('app.name', 'TrendPin'),
            'shop_name' => 'Test Shop',
            'branch_name' => 'Test Branch',
            'branch_address' => '123 Test Street',
            'distance' => '500m',
            'offer_count' => '5',
            'plan_name' => 'Premium Plan',
            'amount' => '$99.00',
            'expiry_date' => now()->addMonth()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'days_left' => '7',
            'admin_message' => 'This is a test message from the admin.',
            'registration_date' => now()->format('Y-m-d H:i'),
            'submission_date' => now()->format('Y-m-d H:i'),
        ];

        $placeholders = $this->getEventPlaceholders($eventId);
        $result = [];

        foreach ($placeholders as $placeholder) {
            $result[$placeholder] = $defaults[$placeholder] ?? "[{$placeholder}]";
        }

        return $result;
    }
}
