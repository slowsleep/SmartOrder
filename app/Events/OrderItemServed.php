<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderItem;

class OrderItemServed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderItem $orderItem;

    /**
     * Create a new event instance.
     */
    public function __construct($orderItem)
    {
        $this->orderItem = $orderItem;
    }
}
