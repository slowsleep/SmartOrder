<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use App\Events\OrderItemPreparing;
use App\Events\OrderItemReady;
use App\Events\OrderItemStatusUpdated;

class CookOrderController extends Controller
{
    use ApiResponse;

    /**
     * Очередь блюд на приготовление
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $orderItems = OrderItem::where('status', OrderItemStatus::PENDING)->orderBy('created_at', 'asc')->with('product')->get();

        return $this->success($orderItems);
    }

    /**
     * Блюда в работе у повара
     * @return \Illuminate\Http\JsonResponse
     */
    public function own()
    {
        $orderItems = OrderItem::where('status', OrderItemStatus::PREPARING)
            ->where('cook_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->with('product')
            ->get();

        return $this->success($orderItems);
    }

    /**
     * Взять блюдо в работу
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function take($id)
    {
        $orderItem = OrderItem::where('status', OrderItemStatus::PENDING)->findOrFail($id);
        $orderItem->update([
            'status' => OrderItemStatus::PREPARING,
            'cook_id' => Auth::id(),
        ]);

        broadcast(new OrderItemPreparing($orderItem));
        broadcast(new OrderItemStatusUpdated($orderItem->order_id, $orderItem->id, $orderItem->status))->toOthers();

        return $this->success([], 'Order item taken');
    }

    /**
     * Блюдо готово к выдаче
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ready($id)
    {
        $orderItem = OrderItem::where('status', OrderItemStatus::PREPARING)
            ->where('cook_id', Auth::id())
            ->findOrFail($id);
        $orderItem->update([
            'status' => OrderItemStatus::READY,
        ]);

        broadcast(new OrderItemReady($orderItem)
        );
        broadcast(new OrderItemStatusUpdated($orderItem->order_id, $orderItem->id, $orderItem->status))->toOthers();

        return $this->success([], 'Order item ready');
    }
}
