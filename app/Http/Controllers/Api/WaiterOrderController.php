<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Facades\Auth;

class WaiterOrderController extends Controller
{
    use ApiResponse;

    public function index(){
        $orderItems = OrderItem::where('status', OrderItemStatus::READY)->get();

        return $this->success($orderItems);
    }

    public function get($id){
        $orderItem = OrderItem::where('status', OrderItemStatus::READY)->findOrFail($id);
        $orderItem->update([
            'status' => OrderItemStatus::IN_DELIVERY,
            'waiter_id' => Auth::id(),
        ]);

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

        return $this->success([], 'Order item served');
    }
}
