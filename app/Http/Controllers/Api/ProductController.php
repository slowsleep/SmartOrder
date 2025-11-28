<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductCreateRequest;
use App\Http\Requests\Products\ProductUpdateRequest;
use App\Models\Product;


class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();

            return response()->json([
                'error' => false,
                'message' => 'Products found successfully',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(ProductCreateRequest $request)
    {
        try {
            $validated = $request->validated();

            $product = Product::create($validated);

            return response()->json([
                'error' => false,
                'message' => 'Product created successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);

            return response()->json([
                'error' => false,
                'message' => 'Product found successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            $product = Product::find($id);
            $product->update($validated);

            return response()->json([
                'error' => false,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            $product->delete();

            return response()->json([
                'error' => false,
                'message' => 'Product deleted successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
