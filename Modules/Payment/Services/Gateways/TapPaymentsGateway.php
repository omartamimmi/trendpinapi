<?php

namespace Modules\Payment\Services\Gateways;

use Modules\Payment\app\DTO\PaymentRequestDTO;
use Modules\Payment\app\DTO\PaymentResponseDTO;

class TapPaymentsGateway extends BaseGateway
{
    public function getIdentifier(): string
    {
        return 'tap';
    }

    public function getDisplayName(): string
    {
        return 'Tap Payments';
    }

    public function supports(string $paymentMethod): bool
    {
        return in_array($paymentMethod, ['card', 'apple_pay', 'google_pay']);
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getCredential('secret_key'),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Initiate payment (creates charge with redirect for 3DS)
     */
    public function initiatePayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        $response = $this->request('POST', 'charges', [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'customer' => [
                'first_name' => $request->customerName ?? 'Customer',
                'email' => $request->customerEmail,
                'phone' => [
                    'country_code' => '962',
                    'number' => $request->customerPhone,
                ],
            ],
            'source' => ['id' => 'src_all'], // Allow all payment sources
            'redirect' => ['url' => $request->redirectUrl],
            'post' => ['url' => $request->webhookUrl ?? route('webhooks.payment.tap')],
            'reference' => [
                'transaction' => $request->reference ?? $this->generateReference(),
                'order' => $request->orderId ?? $request->reference,
            ],
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Payment initiation failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];
        $status = $this->mapStatus($data['status'] ?? 'UNKNOWN');

        if ($status === 'pending' && isset($data['transaction']['url'])) {
            return PaymentResponseDTO::pending(
                $data['id'],
                $data['transaction']['url'],
                $data
            );
        }

        return new PaymentResponseDTO(
            success: true,
            transactionId: $data['id'],
            status: $status,
            amount: $data['amount'] ?? null,
            gatewayResponse: $data,
        );
    }

    /**
     * Authorize payment (hold without capture)
     */
    public function authorizePayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        $response = $this->request('POST', 'authorize', [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'customer' => [
                'first_name' => $request->customerName ?? 'Customer',
                'email' => $request->customerEmail,
            ],
            'source' => ['id' => 'src_all'],
            'auto' => ['type' => 'VOID', 'time' => 168], // Auto void after 7 days
            'redirect' => ['url' => $request->redirectUrl],
            'post' => ['url' => $request->webhookUrl ?? route('webhooks.payment.tap')],
            'reference' => [
                'transaction' => $request->reference ?? $this->generateReference(),
            ],
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Authorization failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: true,
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            redirectUrl: $data['transaction']['url'] ?? null,
            amount: $data['amount'] ?? null,
            card: $this->extractCardInfo($data),
            gatewayResponse: $data,
        );
    }

    /**
     * Capture authorized payment
     */
    public function capturePayment(string $transactionId, float $amount): PaymentResponseDTO
    {
        $response = $this->request('POST', "authorize/{$transactionId}/capture", [
            'amount' => $amount,
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Capture failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: ($data['status'] ?? '') === 'CAPTURED',
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            amount: $data['amount'] ?? null,
            gatewayResponse: $data,
        );
    }

    /**
     * Void authorized payment
     */
    public function voidPayment(string $transactionId): PaymentResponseDTO
    {
        $response = $this->request('POST', "authorize/{$transactionId}/void");

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Void failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: true,
            transactionId: $data['id'],
            status: 'voided',
            gatewayResponse: $data,
        );
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $transactionId, float $amount, ?string $reason = null): PaymentResponseDTO
    {
        $response = $this->request('POST', 'refunds', [
            'charge_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'JOD',
            'reason' => $reason ?? 'Refund requested',
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Refund failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: ($data['status'] ?? '') === 'PENDING' || ($data['status'] ?? '') === 'REFUNDED',
            transactionId: $data['id'],
            status: 'refunded',
            amount: $data['amount'] ?? null,
            gatewayResponse: $data,
        );
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): PaymentResponseDTO
    {
        $response = $this->request('GET', "charges/{$transactionId}");

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                'Failed to retrieve payment status',
                'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: true,
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            amount: $data['amount'] ?? null,
            card: $this->extractCardInfo($data),
            gatewayResponse: $data,
        );
    }

    /**
     * Tokenize card
     */
    public function tokenizeCard(array $cardData, array $customerData = []): array
    {
        // Step 1: Create token
        $tokenResponse = $this->request('POST', 'tokens', [
            'card' => [
                'number' => $cardData['card_number'],
                'exp_month' => $cardData['exp_month'],
                'exp_year' => $cardData['exp_year'],
                'cvc' => $cardData['cvv'],
                'name' => $cardData['cardholder_name'] ?? '',
            ],
        ]);

        if (!$tokenResponse['success']) {
            throw new \Exception(
                $tokenResponse['data']['errors'][0]['description'] ?? 'Tokenization failed'
            );
        }

        $tokenData = $tokenResponse['data'];
        $token = $tokenData['id'];

        // Step 2: Create or get customer
        $customerId = $customerData['gateway_customer_id'] ?? null;

        if (!$customerId) {
            $customerResponse = $this->request('POST', 'customers', [
                'first_name' => $customerData['name'] ?? 'Customer',
                'email' => $customerData['email'] ?? '',
                'phone' => [
                    'country_code' => '962',
                    'number' => $customerData['phone'] ?? '',
                ],
            ]);

            if ($customerResponse['success']) {
                $customerId = $customerResponse['data']['id'];
            }
        }

        // Step 3: Save card to customer
        if ($customerId) {
            $this->request('POST', "customers/{$customerId}/cards", [
                'source' => $token,
            ]);
        }

        return [
            'token' => $token,
            'customer_id' => $customerId,
            'card' => [
                'last_four' => $tokenData['card']['last_four'] ?? substr($cardData['card_number'], -4),
                'brand' => strtolower($tokenData['card']['brand'] ?? 'unknown'),
                'exp_month' => $tokenData['card']['exp_month'] ?? $cardData['exp_month'],
                'exp_year' => $tokenData['card']['exp_year'] ?? $cardData['exp_year'],
                'bin' => $tokenData['card']['first_six'] ?? substr($cardData['card_number'], 0, 6),
            ],
        ];
    }

    /**
     * Charge tokenized card
     */
    public function chargeToken(string $token, string $customerId, PaymentRequestDTO $request): PaymentResponseDTO
    {
        $response = $this->request('POST', 'charges', [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'source' => ['id' => $token],
            'customer' => ['id' => $customerId],
            'reference' => [
                'transaction' => $request->reference ?? $this->generateReference(),
                'order' => $request->orderId ?? $request->reference,
            ],
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Charge failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: ($data['status'] ?? '') === 'CAPTURED',
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            amount: $data['amount'] ?? null,
            chargeId: $data['id'],
            card: $this->extractCardInfo($data),
            gatewayResponse: $data,
        );
    }

    /**
     * Delete tokenized card
     */
    public function deleteToken(string $token, string $customerId): bool
    {
        $response = $this->request('DELETE', "customers/{$customerId}/cards/{$token}");
        return $response['success'];
    }

    /**
     * Process Apple Pay token
     */
    public function processApplePayToken(string $token, PaymentRequestDTO $request): PaymentResponseDTO
    {
        $response = $this->request('POST', 'charges', [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'source' => [
                'id' => 'src_apple_pay',
                'token' => $token,
            ],
            'customer' => [
                'first_name' => $request->customerName ?? 'Customer',
                'email' => $request->customerEmail,
            ],
            'reference' => [
                'transaction' => $request->reference ?? $this->generateReference(),
            ],
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Apple Pay charge failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: in_array($data['status'] ?? '', ['CAPTURED', 'AUTHORIZED']),
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            amount: $data['amount'] ?? null,
            card: $this->extractCardInfo($data),
            gatewayResponse: $data,
        );
    }

    /**
     * Process Google Pay token
     */
    public function processGooglePayToken(string $token, PaymentRequestDTO $request): PaymentResponseDTO
    {
        $response = $this->request('POST', 'charges', [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'source' => [
                'id' => 'src_google_pay',
                'token' => $token,
            ],
            'customer' => [
                'first_name' => $request->customerName ?? 'Customer',
                'email' => $request->customerEmail,
            ],
            'reference' => [
                'transaction' => $request->reference ?? $this->generateReference(),
            ],
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);

        if (!$response['success']) {
            return PaymentResponseDTO::error(
                $response['data']['errors'][0]['description'] ?? 'Google Pay charge failed',
                $response['data']['errors'][0]['code'] ?? 'UNKNOWN',
                $response['data']
            );
        }

        $data = $response['data'];

        return new PaymentResponseDTO(
            success: in_array($data['status'] ?? '', ['CAPTURED', 'AUTHORIZED']),
            transactionId: $data['id'],
            status: $this->mapStatus($data['status'] ?? 'UNKNOWN'),
            amount: $data['amount'] ?? null,
            card: $this->extractCardInfo($data),
            gatewayResponse: $data,
        );
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->getCredential('webhook_secret');

        if (empty($secret)) {
            return true; // Skip verification if no secret configured
        }

        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }

    /**
     * Parse webhook payload
     */
    public function parseWebhookPayload(array $payload): array
    {
        return [
            'event_type' => $payload['object'] ?? 'charge',
            'transaction_id' => $payload['id'] ?? null,
            'status' => $this->mapStatus($payload['status'] ?? 'UNKNOWN'),
            'amount' => $payload['amount'] ?? 0,
            'reference' => $payload['reference']['transaction'] ?? null,
            'card' => $this->extractCardInfo($payload),
            'raw' => $payload,
        ];
    }

    /**
     * Map Tap status to standard status
     */
    private function mapStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'INITIATED' => 'pending',
            'IN_PROGRESS' => 'processing',
            'CAPTURED' => 'completed',
            'AUTHORIZED' => 'authorized',
            'FAILED', 'DECLINED', 'RESTRICTED' => 'failed',
            'CANCELLED', 'ABANDONED', 'TIMEDOUT' => 'cancelled',
            'REFUNDED' => 'refunded',
            'VOID', 'VOIDED' => 'voided',
            default => 'unknown',
        };
    }

    /**
     * Extract card info from response
     */
    private function extractCardInfo(array $data): array
    {
        $card = $data['card'] ?? $data['source']['card'] ?? [];

        return [
            'last_four' => $card['last_four'] ?? null,
            'brand' => strtolower($card['brand'] ?? ''),
            'exp_month' => $card['exp_month'] ?? null,
            'exp_year' => $card['exp_year'] ?? null,
            'bin' => $card['first_six'] ?? null,
            'scheme' => $card['scheme'] ?? null,
        ];
    }
}
