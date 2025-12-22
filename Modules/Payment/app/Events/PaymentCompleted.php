<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\PaymentTransaction;

class PaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PaymentTransaction $payment;

    public function __construct(PaymentTransaction $payment)
    {
        $this->payment = $payment->load(['brand', 'branch']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customer.' . $this->payment->customer_id . '.payments'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.completed';
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
            'original_amount' => (float) $this->payment->original_amount,
            'discount_amount' => (float) $this->payment->discount_amount,
            'currency' => $this->payment->currency,
            'retailer' => $this->payment->brand?->name ?? $this->payment->retailer_name,
            'branch' => $this->payment->branch?->name ?? $this->payment->branch_name,
            'completed_at' => $this->payment->completed_at?->toIso8601String(),
        ];
    }
}
