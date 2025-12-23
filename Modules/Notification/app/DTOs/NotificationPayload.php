<?php

namespace Modules\Notification\app\DTOs;

/**
 * Data Transfer Object for notification payload
 */
class NotificationPayload
{
    public function __construct(
        public readonly string $recipientId,
        public readonly string $recipientType, // admin, retailer, customer
        public readonly string $recipientContact, // email, phone, or device token
        public readonly string $subject,
        public readonly string $body,
        public readonly ?string $title = null, // for push notifications
        public readonly array $data = [], // additional data
        public readonly ?string $templateId = null,
        public readonly array $placeholders = [],
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            recipientId: $data['recipient_id'] ?? '',
            recipientType: $data['recipient_type'] ?? 'customer',
            recipientContact: $data['recipient_contact'] ?? '',
            subject: $data['subject'] ?? '',
            body: $data['body'] ?? '',
            title: $data['title'] ?? null,
            data: $data['data'] ?? [],
            templateId: $data['template_id'] ?? null,
            placeholders: $data['placeholders'] ?? [],
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'recipient_id' => $this->recipientId,
            'recipient_type' => $this->recipientType,
            'recipient_contact' => $this->recipientContact,
            'subject' => $this->subject,
            'body' => $this->body,
            'title' => $this->title,
            'data' => $this->data,
            'template_id' => $this->templateId,
            'placeholders' => $this->placeholders,
        ];
    }

    /**
     * Replace placeholders in subject and body
     */
    public function withReplacedPlaceholders(array $values): self
    {
        $subject = $this->subject;
        $body = $this->body;
        $title = $this->title;

        foreach ($values as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
            if ($title) {
                $title = str_replace($placeholder, $value, $title);
            }
        }

        return new self(
            recipientId: $this->recipientId,
            recipientType: $this->recipientType,
            recipientContact: $this->recipientContact,
            subject: $subject,
            body: $body,
            title: $title,
            data: $this->data,
            templateId: $this->templateId,
            placeholders: $values,
        );
    }
}
