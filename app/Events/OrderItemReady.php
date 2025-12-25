<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderItem;

    /**
     * Create a new event instance.
     */
    public function __construct($orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->orderItem->cook_id . '.preparing-items'), // убрать блюдо в личном списке повара
            new PrivateChannel('waiters.ready-items'), // добавить блюдо в общий список официантов
        ];
    }

    public function broadcastWith()
    {
        return [
            'orderItem' => $this->orderItem->load('order.table'),
        ];
    }

    public function broadcastAs()
    {
        return 'order-item.ready';
    }
}
