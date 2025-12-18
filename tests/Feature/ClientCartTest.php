<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;

/**
 * Tests for Api/ClientCartController
 */
class ClientCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_cart_session()
    {
        $response = $this->getJson('/api/cart');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'items' => []
            ]
        ]);

        $data = $response->json();
        $this->assertIsArray($data['data']['items']);
        $this->assertEmpty($data['data']['items']);
    }

    public function test_cart_persists_between_requests()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $this->postJson('/api/cart/add', ['product_id' => $product->id]);

        $response = $this->getJson('/api/cart');
        $response->assertJsonFragment(['product_id' => $product->id]);
    }

    public function test_add_to_cart_with_session()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(200);

        $this->assertNotNull(session('cart'));
        $this->assertArrayHasKey($product->id, session('cart'));
    }

    public function test_two_equal_products_in_cart()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(200);

        $this->assertNotNull(session('cart'));
        $this->assertArrayHasKey($product->id, session('cart'));
        $this->assertEquals(2, session('cart')[$product->id]['quantity']);
    }

    public function test_remove_from_cart()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response = $this->postJson('/api/cart/remove', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(200);

        $this->assertNotNull(session('cart'));
        $this->assertArrayNotHasKey($product->id, session('cart'));
    }

    public function test_add_two_remove_one_product_from_cart()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product->id
        ]);

        $response = $this->postJson('/api/cart/decrease', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(200);

        $this->assertNotNull(session('cart'));
        $this->assertEquals(1, session('cart')[$product->id]['quantity']);
    }

    public function test_add_two_different_products_to_cart()
    {
        $product1 = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $product2 = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product1->id
        ]);

        $response = $this->postJson('/api/cart/add', [
            'product_id' => $product2->id
        ]);

        $response->assertStatus(200);

        $response = $this->getJson('/api/cart');

        $this->assertNotNull(session('cart'));
        $this->assertEquals(2, count($response['data']['items']));
        $this->assertArrayHasKey($product1->id, session('cart'));
        $this->assertArrayHasKey($product2->id, session('cart'));
    }

    public function test_clear_cart()
    {
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        $this->postJson('/api/cart/add', ['product_id' => $product->id]);

        $this->postJson('/api/cart/clear');
        $this->assertEmpty(session('cart'));
    }

}
