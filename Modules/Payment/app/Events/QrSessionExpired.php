<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\QrPaymentSession;

class QrSessionExpired implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public QrPaymentSession $session;

    public function __construct(QrPaymentSession $session)
    {
        $this->session = $session;
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

        // Also notify the customer if they scanned the QR
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
        return 'session.expired';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_code' => $this->session->session_code,
            'amount' => (float) $this->session->amount,
            'expired_at' => $this->session->expires_at?->toIso8601String(),
        ];
    }
}
