<?php

namespace Modules\Payment\app\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Payment\app\Models\QrPaymentSession;

class QrSessionScanned implements ShouldBroadcast
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
        return 'session.scanned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_code' => $this->session->session_code,
            'customer' => $this->session->customer ? [
                'name' => $this->maskName($this->session->customer->name),
                'avatar' => $this->session->customer->avatar_url ?? null,
            ] : null,
            'scanned_at' => $this->session->scanned_at?->toIso8601String(),
            'amount' => (float) $this->session->amount,
            'currency' => $this->session->currency,
        ];
    }

    /**
     * Mask customer name for privacy
     */
    private function maskName(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            return $parts[0] . ' ' . substr($parts[1], 0, 1) . '.';
        }
        return substr($name, 0, 3) . '***';
    }
}
