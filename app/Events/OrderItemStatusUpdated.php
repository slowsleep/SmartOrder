<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $orderId;
    public $orderItemId;
    public $orderItemStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($orderId, $orderItemId, $orderItemStatus)
    {
        $this->orderId = $orderId;
        $this->orderItemId = $orderItemId;
        $this->orderItemStatus = $orderItemStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('order.' . $this->orderId), // обновить стратус блюда для клиента
        ];
    }

    public function broadcastAs()
    {
        return 'order-item.status-updated';
    }
}
