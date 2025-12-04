<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class CookOrderController extends Controller
{
    use ApiResponse;

    /**
     * Очередь блюд на приготовление
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $orderItems = OrderItem::where('status', OrderItemStatus::PENDING)->orderBy('created_at', 'asc')->get();

        return $this->success($orderItems);
    }

    /**
     * Взять блюдо в работу
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {
        $orderItem = OrderItem::where('status', OrderItemStatus::PENDING)->findOrFail($id);
        $orderItem->update([
            'status' => OrderItemStatus::PREPARING,
            'cook_id' => Auth::id(),
        ]);

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

        return $this->success([], 'Order item ready');
    }
}
