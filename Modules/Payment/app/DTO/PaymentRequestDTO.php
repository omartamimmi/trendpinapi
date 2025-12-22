<?php

namespace Modules\Payment\app\DTO;

class PaymentRequestDTO
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'JOD',
        public readonly ?string $reference = null,
        public readonly ?string $orderId = null,
        public readonly ?int $customerId = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerPhone = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $webhookUrl = null,
        public readonly ?string $gatewayCustomerId = null,
        public readonly ?int $branchId = null,
        public readonly ?int $brandId = null,
        public readonly ?string $cardBin = null,
        public readonly ?string $description = null,
        public readonly bool $saveCard = false,
        public readonly ?string $cardNickname = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) ($data['amount'] ?? 0),
            currency: $data['currency'] ?? 'JOD',
            reference: $data['reference'] ?? 'PAY-' . uniqid(),
            orderId: $data['order_id'] ?? null,
            customerId: $data['customer_id'] ?? null,
            customerName: $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            redirectUrl: $data['redirect_url'] ?? null,
            webhookUrl: $data['webhook_url'] ?? null,
            gatewayCustomerId: $data['gateway_customer_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            brandId: $data['brand_id'] ?? null,
            cardBin: $data['card_bin'] ?? null,
            description: $data['description'] ?? null,
            saveCard: $data['save_card'] ?? false,
            cardNickname: $data['card_nickname'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'redirect_url' => $this->redirectUrl,
            'webhook_url' => $this->webhookUrl,
            'branch_id' => $this->branchId,
            'brand_id' => $this->brandId,
            'card_bin' => $this->cardBin,
            'description' => $this->description,
            'save_card' => $this->saveCard,
            'metadata' => $this->metadata,
        ];
    }
}
