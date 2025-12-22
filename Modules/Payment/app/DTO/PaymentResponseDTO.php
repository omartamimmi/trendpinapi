<?php

namespace Modules\Payment\app\DTO;

class PaymentResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly string $status = 'unknown',
        public readonly ?string $redirectUrl = null,
        public readonly ?float $amount = null,
        public readonly ?string $chargeId = null,
        public readonly ?string $authorizationCode = null,
        public readonly array $card = [],
        public readonly array $gatewayResponse = [],
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorCode = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->success && in_array($this->status, ['completed', 'captured', 'authorized']);
    }

    public function requiresRedirect(): bool
    {
        return $this->status === 'pending' && !empty($this->redirectUrl);
    }

    public function requiresAuth(): bool
    {
        return $this->status === 'requires_action' && !empty($this->redirectUrl);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'status' => $this->status,
            'redirect_url' => $this->redirectUrl,
            'amount' => $this->amount,
            'charge_id' => $this->chargeId,
            'authorization_code' => $this->authorizationCode,
            'card' => $this->card,
            'error_message' => $this->errorMessage,
            'error_code' => $this->errorCode,
        ];
    }

    public static function success(
        string $transactionId,
        string $status = 'completed',
        ?float $amount = null,
        array $card = [],
        array $gatewayResponse = []
    ): self {
        return new self(
            success: true,
            transactionId: $transactionId,
            status: $status,
            amount: $amount,
            card: $card,
            gatewayResponse: $gatewayResponse,
        );
    }

    public static function pending(
        string $transactionId,
        string $redirectUrl,
        array $gatewayResponse = []
    ): self {
        return new self(
            success: true,
            transactionId: $transactionId,
            status: 'pending',
            redirectUrl: $redirectUrl,
            gatewayResponse: $gatewayResponse,
        );
    }

    public static function error(
        string $errorMessage,
        ?string $errorCode = null,
        array $gatewayResponse = []
    ): self {
        return new self(
            success: false,
            status: 'failed',
            errorMessage: $errorMessage,
            errorCode: $errorCode,
            gatewayResponse: $gatewayResponse,
        );
    }
}
