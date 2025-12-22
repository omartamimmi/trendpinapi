<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\QrPaymentSession;

class QrSessionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public QrPaymentSession $session;

    public function __construct(QrPaymentSession $session)
    {
        $this->session = $session->load(['customer', 'payment', 'bankOffer.bank']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('retailer.' . $this->session->retailer_id . '.qr-sessions'),
        ];

        // Also notify the customer
        if ($this->session->customer_id) {
            $channels[] = new PrivateChannel('customer.' . $this->session->customer_id . '.payments');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_code' => $this->session->session_code,
            'payment_id' => $this->session->payment_id,
            'transaction_id' => $this->session->gateway_transaction_id,
            'original_amount' => (float) $this->session->original_amount,
            'discount_amount' => (float) $this->session->discount_amount,
            'final_amount' => (float) $this->session->final_amount,
            'currency' => $this->session->currency,
            'payment_method' => $this->session->payment_method,
            'gateway' => $this->session->gateway,
            'customer' => $this->session->customer ? [
                'name' => $this->session->customer->name,
                'phone_last_four' => $this->session->customer->phone
                    ? substr($this->session->customer->phone, -4)
                    : null,
            ] : null,
            'card' => $this->session->payment ? [
                'last_four' => $this->session->payment->card_last_four,
                'brand' => $this->session->payment->card_brand,
            ] : null,
            'bank_offer' => $this->session->bankOffer ? [
                'bank_name' => $this->session->bankOffer->bank?->name,
                'bank_logo' => $this->session->bankOffer->bank?->logo?->url,
                'offer_display' => $this->session->bankOffer->discount_display,
            ] : null,
            'completed_at' => $this->session->completed_at?->toIso8601String(),
        ];
    }
}
