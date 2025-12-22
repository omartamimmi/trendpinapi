<?php

namespace Modules\Payment\Services\Contracts;

use Modules\Payment\app\DTO\PaymentRequestDTO;
use Modules\Payment\app\DTO\PaymentResponseDTO;

interface PaymentGatewayInterface
{
    /**
     * Get gateway identifier (e.g., 'tap', 'hyperpay', 'paytabs')
     */
    public function getIdentifier(): string;

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string;

    /**
     * Check if gateway supports a payment method
     * @param string $paymentMethod (card, apple_pay, google_pay)
     */
    public function supports(string $paymentMethod): bool;

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool;

    /**
     * Initiate a payment - returns redirect URL for 3DS
     */
    public function initiatePayment(PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Authorize a payment (hold without capture)
     */
    public function authorizePayment(PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Capture a pre-authorized payment
     */
    public function capturePayment(string $transactionId, float $amount): PaymentResponseDTO;

    /**
     * Void a pre-authorized payment
     */
    public function voidPayment(string $transactionId): PaymentResponseDTO;

    /**
     * Refund a completed payment
     */
    public function refundPayment(string $transactionId, float $amount, ?string $reason = null): PaymentResponseDTO;

    /**
     * Get current payment status from gateway
     */
    public function getPaymentStatus(string $transactionId): PaymentResponseDTO;

    /**
     * Tokenize a card for future payments
     * @return array ['token' => string, 'customer_id' => string, 'card' => array]
     */
    public function tokenizeCard(array $cardData, array $customerData = []): array;

    /**
     * Charge a tokenized card (no redirect needed)
     */
    public function chargeToken(string $token, string $customerId, PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Delete a tokenized card from gateway
     */
    public function deleteToken(string $token, string $customerId): bool;

    /**
     * Process Apple Pay token
     */
    public function processApplePayToken(string $token, PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Process Google Pay token
     */
    public function processGooglePayToken(string $token, PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Verify webhook signature for security
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Parse webhook payload into standard format
     */
    public function parseWebhookPayload(array $payload): array;
}
