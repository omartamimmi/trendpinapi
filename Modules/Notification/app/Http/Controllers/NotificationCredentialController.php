<?php

namespace Modules\Notification\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Notification\app\Services\CredentialService;
use Modules\Notification\app\Services\NotificationTestService;
use Illuminate\Validation\Rule;

/**
 * Controller for notification credential management and testing
 */
class NotificationCredentialController extends Controller
{
    private CredentialService $credentialService;
    private NotificationTestService $testService;

    public function __construct()
    {
        $this->credentialService = new CredentialService();
        $this->testService = new NotificationTestService();
    }

    /**
     * Get all credential statuses
     */
    public function getStatuses(): JsonResponse
    {
        $statuses = $this->credentialService->getAllStatuses();

        // Convert to array format expected by frontend
        $formattedStatuses = [];
        foreach ($statuses as $channel => $status) {
            $formattedStatuses[] = [
                'channel' => $channel,
                'status' => $status,
                'is_configured' => $status === 'configured',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedStatuses,
        ]);
    }

    /**
     * Get credentials for a channel (masked)
     */
    public function getCredentials(string $channel): JsonResponse
    {
        $credentials = $this->credentialService->getCredentials($channel);

        if (!$credentials) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $credentials->toArray(true), // masked
        ]);
    }

    /**
     * Get all credentials (masked)
     */
    public function getAllCredentials(): JsonResponse
    {
        $credentials = $this->credentialService->getAllCredentials();

        return response()->json([
            'success' => true,
            'data' => $credentials,
        ]);
    }

    /**
     * Save credentials for a channel
     */
    public function saveCredentials(Request $request, string $channel): JsonResponse
    {
        $rules = $this->getValidationRules($channel);

        $validated = $request->validate($rules);

        try {
            $credentials = $this->credentialService->saveCredentials($channel, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Credentials saved successfully',
                'data' => $credentials->toArray(true),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test credentials for a channel
     */
    public function testCredentials(Request $request, string $channel): JsonResponse
    {
        // Optionally accept credentials to test without saving
        $credentials = $request->all();

        try {
            $result = $this->credentialService->testCredentials(
                $channel,
                !empty($credentials) ? $credentials : null
            );

            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
                'details' => $result->details,
                'metadata' => $result->metadata,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete credentials for a channel
     */
    public function deleteCredentials(string $channel): JsonResponse
    {
        try {
            $deleted = $this->credentialService->deleteCredentials($channel);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Credentials deleted' : 'No credentials found',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle channel active status
     */
    public function toggleActive(Request $request, string $channel): JsonResponse
    {
        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        try {
            $this->credentialService->setChannelActive($channel, $validated['active']);

            return response()->json([
                'success' => true,
                'message' => 'Channel status updated',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available providers for a channel
     */
    public function getProviders(string $channel): JsonResponse
    {
        $providers = $this->credentialService->getSupportedProviders($channel);

        return response()->json([
            'success' => true,
            'data' => $providers,
        ]);
    }

    /**
     * Get recipients for testing
     */
    public function getRecipients(string $type): JsonResponse
    {
        $recipients = $this->testService->getRecipientsByType($type);

        return response()->json([
            'success' => true,
            'data' => $recipients,
        ]);
    }

    /**
     * Get available test events
     */
    public function getTestEvents(): JsonResponse
    {
        $events = $this->testService->getAvailableEvents();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get placeholders for an event
     */
    public function getEventPlaceholders(string $eventId): JsonResponse
    {
        $placeholders = $this->testService->getEventPlaceholders($eventId);
        $defaults = $this->testService->getDefaultPlaceholderValues($eventId);

        return response()->json([
            'success' => true,
            'data' => [
                'placeholders' => $placeholders,
                'defaults' => $defaults,
            ],
        ]);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['email', 'smtp', 'sms', 'whatsapp', 'push'])],
            'recipient_type' => ['required', Rule::in(['admin', 'retailer', 'customer'])],
            'recipient_id' => 'required|string',
            'event_id' => 'required|string',
            'placeholders' => 'nullable|array',
        ]);

        // Normalize channel name
        $channel = $validated['channel'] === 'email' ? 'smtp' : $validated['channel'];

        try {
            $result = $this->testService->sendTest(
                $channel,
                $validated['recipient_type'],
                $validated['recipient_id'],
                $validated['event_id'],
                $validated['placeholders'] ?? []
            );

            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
                'message_id' => $result->messageId,
                'metadata' => $result->metadata,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get validation rules for a channel
     */
    private function getValidationRules(string $channel): array
    {
        return match ($channel) {
            'smtp', 'email' => [
                'provider' => 'nullable|string',
                'host' => 'required|string',
                'port' => 'required|string',
                'username' => 'required|string',
                'password' => 'required|string',
                'encryption' => 'nullable|in:tls,ssl,none',
                'from_address' => 'required|email',
                'from_name' => 'nullable|string',
            ],
            'sms' => [
                'provider' => 'required|string|in:twilio,nexmo,messagebird',
                'account_sid' => 'required_if:provider,twilio|nullable|string',
                'auth_token' => 'required|string',
                'from_number' => 'required|string',
                'api_key' => 'required_if:provider,nexmo,messagebird|nullable|string',
                'api_secret' => 'required_if:provider,nexmo,messagebird|nullable|string',
            ],
            'whatsapp' => [
                'provider' => 'required|string|in:twilio,meta',
                'account_sid' => 'required_if:provider,twilio|nullable|string',
                'auth_token' => 'required_if:provider,twilio|nullable|string',
                'from_number' => 'required_if:provider,twilio|nullable|string',
                'business_id' => 'required_if:provider,meta|nullable|string',
                'phone_number_id' => 'required_if:provider,meta|nullable|string',
                'access_token' => 'required_if:provider,meta|nullable|string',
            ],
            'push' => [
                'provider' => 'required|string|in:firebase',
                'project_id' => 'required|string',
                'server_key' => 'nullable|string',
                'service_account_json' => 'required|string',
            ],
            default => [],
        };
    }
}
