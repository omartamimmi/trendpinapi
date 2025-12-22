<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\PaymentTransaction;

class PaymentFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PaymentTransaction $payment;
    public string $errorMessage;

    public function __construct(PaymentTransaction $payment, string $errorMessage = 'Payment failed')
    {
        $this->payment = $payment;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->payment->customer_id) {
            $channels[] = new PrivateChannel('customer.' . $this->payment->customer_id . '.payments');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.failed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'amount' => (float) $this->payment->amount,
            'error' => $this->errorMessage,
            'failed_at' => now()->toIso8601String(),
        ];
    }
}
