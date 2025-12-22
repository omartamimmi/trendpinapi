<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\QrPaymentSession;

class QrSessionProcessing implements ShouldBroadcast
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
        return [
            new PrivateChannel('retailer.' . $this->session->retailer_id . '.qr-sessions'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.processing';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_code' => $this->session->session_code,
            'original_amount' => (float) $this->session->original_amount,
            'discount_amount' => (float) $this->session->discount_amount,
            'final_amount' => (float) $this->session->final_amount,
            'payment_method' => $this->session->payment_method,
            'gateway' => $this->session->gateway,
        ];
    }
}
