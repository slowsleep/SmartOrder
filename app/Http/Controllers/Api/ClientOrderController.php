<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\OrderItemStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;
use App\Events\OrderCreated;

class ClientOrderController extends Controller
{
    use ApiResponse;

    public function store()
    {
        // 1. Проверяем сессию стола
        $tableSession = session('table_session');

        if (!$tableSession || now()->gt($tableSession['expires_at'])) {
            return $this->error('Table session expired', 403);
        }

        // 2. Проверяем корзину
        $cart = session('cart', []);

        if (empty($cart)) {
            return $this->error('Cart is empty', 400);
        }

        // 3. Создаем заказ
        $order = Order::create([
            'table_id' => $tableSession['table_id'],
            'status' => OrderStatus::PENDING->value,
            'guest_token' => Str::random(32), // уникальный токен для гостя
            'expires_at' => now()->addHours(2), // можно смотреть 2 часа
        ]);

        foreach ($cart as $item) {
            Collection::times($item['quantity'], function () use ($order, $item) {
                OrderItem::withoutEvents(function () use ($order, $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'unit_price' => $item['price'],
                        'status'     => OrderItemStatus::PENDING->value,
                    ]);
                });
            });
        }

        session()->forget('cart');
        session()->put("order_{$order->id}_token", $order->guest_token);

        return $this->success([
            'guest_token' => $order->guest_token, // секретный токен гостя (клиента, что сделал заказ)
            'order_id' => $order->id,
        ], 'Order created');
    }

    public function pay(Request $request, $orderId)
    {
        // Ищем токен сначала в заголовке, потом в сессии
        $guestToken = $request->header('X-Guest-Token')
                    ?? session()->get("order_{$orderId}_token");

        $order = Order::where('id', $orderId)
            ->where('guest_token', $guestToken)
            ->firstOrFail();

        if ($order->status !== OrderStatus::PENDING->value) {
            return $this->error('Order already paid', 400);
        }

        $order->update([
            'status' => OrderStatus::CONFIRMED->value,
            'paid_at' => now(),
        ]);

        broadcast(new OrderCreated($order))->toOthers();

        return $this->success([], 'Payment successful');
    }

    public function status(Request $request, $orderId)
    {
        $guestToken = $request->header('X-Guest-Token')
                    ?? session()->get("order_{$orderId}_token");

        $order = Order::with('items.product')
            ->where('id', $orderId)
            ->where('guest_token', $guestToken) // только свой заказ
            ->where('expires_at', '>', now())   // только не истекшие
            ->firstOrFail();

        return $this->success($order, 'Order found successfully');
    }
}
