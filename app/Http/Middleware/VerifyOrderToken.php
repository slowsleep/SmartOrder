<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;

class VerifyOrderToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orderId = $request->route('orderId');
        $guestToken = $request->header('X-Guest-Token')
                    ?? session()->get("order_{$orderId}_token");

        if (!$guestToken) {
            return response()->json(['error' => true, 'message' => 'Token required'], 401);
        }

        // Проверяем что заказ существует и токен верный
        $orderExists = Order::where('id', $orderId)
            ->where('guest_token', $guestToken)
            ->first();

        if (!$orderExists) {
            return response()->json(['error' => true, 'message' => 'Order not found'], 404);
        }

        // Проверяем, что токен не устарел
        if (now()->gt($orderExists->expires_at)) {
            return response()->json(['error' => true, 'message' => 'Order expired'], 403);
        }

        return $next($request);
    }
}
