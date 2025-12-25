<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemInDelivery implements ShouldBroadcast
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
            new PrivateChannel('waiters.ready-items'), // убрать блюдо из общего списка официантов
            new PrivateChannel('user.' . $this->orderItem->waiter_id . '.delivery-items'), // добавить блюдо в личный спискок официанта
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
        return 'order-item.in-delivery';
    }
}
