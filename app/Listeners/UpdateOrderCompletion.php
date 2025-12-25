<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderItemServed;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;

class UpdateOrderCompletion
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderItemServed $event): void
    {

        $order = $event->orderItem->order;

        if ($order->items()->where('status', '!=', OrderItemStatus::SERVED)->count() === 0){
            $order->status = OrderStatus::COMPLETED;
            $order->save();
            broadcast(new OrderStatusUpdated($order->id, $order->status));
        }
    }
}
