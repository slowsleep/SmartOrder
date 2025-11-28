<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ClientMenuController extends Controller
{
    public function index()
    {
        try {
            $products = Product::query()->where('is_active', true)->where('quantity', '>', 0)->get();

            return response()->json([
                'error' => false,
                'message' => 'Products found successfully',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::query()->where('is_active', true)->where('quantity', '>', 0)->find($id);

            if (empty($product)) {
                return response()->json(['error' => true, 'message' => 'Product not found'], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Product found successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
