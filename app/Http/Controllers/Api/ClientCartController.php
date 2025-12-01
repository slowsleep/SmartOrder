<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\ClientCart\ClientCartRequest;

class ClientCartController extends Controller
{
    public function index()
    {
        return response()->json([
            'items' => session('cart', []),
        ]);
    }

    public function add(ClientCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session('cart', []);

        // если уже есть — увеличиваем количество
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => 1,
            ];
        }

        session(['cart' => $cart]);

        return response()->json(['cart' => $cart]);
    }

    public function decrease(ClientCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session('cart', []);

        // если уже есть — уменьшаем количество
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']--;

            // если стало 0 — удаляем товар
            if ($cart[$product->id]['quantity'] <= 0) {
                unset($cart[$product->id]);
            }
        }

        session(['cart' => $cart]);

        return response()->json(['cart' => $cart]);
    }

    public function remove(ClientCartRequest $request)
    {
        $cart = session('cart', []);

        unset($cart[$request->product_id]);

        session(['cart' => $cart]);

        return response()->json(['cart' => $cart]);
    }

    public function clear()
    {
        session()->forget('cart');

        return response()->json(['message' => 'Cart cleared']);
    }
}
