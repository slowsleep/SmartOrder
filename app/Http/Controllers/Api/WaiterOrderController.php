<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Facades\Auth;
use App\Events\OrderItemInDelivery;
use App\Events\OrderItemServed;
use App\Events\OrderItemStatusUpdated;

class WaiterOrderController extends Controller
{
    use ApiResponse;

    public function index(){
        $orderItems = OrderItem::where('status', OrderItemStatus::READY)->with('product', 'order.table')->get();

        return $this->success($orderItems);
    }

    public function own(){
        $orderItems = OrderItem::where('status', OrderItemStatus::IN_DELIVERY)
            ->where('waiter_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->with('product', 'order.table')
            ->get();

        return $this->success($orderItems);
    }

    public function take($id){
        $orderItem = OrderItem::where('status', OrderItemStatus::READY)->findOrFail($id);
        $orderItem->update([
            'status' => OrderItemStatus::IN_DELIVERY,
            'waiter_id' => Auth::id(),
        ]);

        broadcast(new OrderItemInDelivery($orderItem));
        broadcast(new OrderItemStatusUpdated($orderItem->order_id, $orderItem->id, $orderItem->status))->toOthers();

        return $this->success([], 'Order item taken');
    }

    public function served($id){
        $orderItem = OrderItem::where('status', OrderItemStatus::IN_DELIVERY)
            ->where('waiter_id', Auth::id())
            ->findOrFail($id);

        $orderItem->update([
            'status' => OrderItemStatus::SERVED,
            'served_at' => now(),
        ]);

        broadcast(new OrderItemServed($orderItem));
        broadcast(new OrderItemStatusUpdated($orderItem->order_id, $orderItem->id, $orderItem->status))->toOthers();

        return $this->success([], 'Order item served');
    }
}
