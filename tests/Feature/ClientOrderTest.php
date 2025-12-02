<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Models\Table;

class ClientOrderTest extends TestCase
{
    use RefreshDatabase;

    // Для каждого запроса надо:
    // создать стол
    // создать продукты
    // добавить в корзину
    protected function setUp(): void
    {
        parent::setUp();

        $table = Table::factory()->create();

        // Создаем тестовые продукты
        Product::withoutEvents(function () {
            Product::factory()->create([
                'id' => 1,
                'name' => 'Pizza Margarita',
                'price' => 12.99,
                'quantity' => 10
            ]);

            Product::factory()->create([
                'id' => 2,
                'name' => 'Burger Classic',
                'price' => 8.99,
                'quantity' => 15
            ]);

            Product::factory()->create([
                'id' => 3,
                'name' => 'Coca-Cola',
                'price' => 2.49,
                'quantity' => 20
            ]);
        });

        // Инициируем сессию стола
        $this->get(route('table.init', ['qr_token' => $table->qr_token]));

        // Добавляем продукты в корзину
        $this->postJson('/api/cart/add', [
            'product_id' => 1
        ]);
        $this->postJson('/api/cart/add', [
            'product_id' => 2
        ]);
        $this->postJson('/api/cart/add', [
            'product_id' => 2
        ]);
        $this->postJson('/api/cart/add', [
            'product_id' => 3
        ]);
        $this->postJson('/api/cart/add', [
            'product_id' => 3
        ]);
    }

    // Позитивные кейсы:
    // - создать заказ
    // - оплатить заказ
    // - получить статус заказа

    private $guestToken;
    private $orderId;

    public function test_create_order_and_get_token()
    {
        $response = $this->postJson('/api/order');
        $response->assertStatus(200)
        ->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'guest_token',
                'order_id'
            ]
        ]);
        $response->assertJson(['message' => 'Order created']);

        // Сохраняем для следующих тестов
        $data = $response->json();
        $this->guestToken = $data['data']['guest_token'];
        $this->orderId = $data['data']['order_id'];

        $this->assertNotNull($this->guestToken);
        $this->assertNotNull($this->orderId);

        // Проверяем в базе
        $this->assertDatabaseHas('orders', [
            'id' => $this->orderId,
            'guest_token' => $this->guestToken
        ]);
    }

    public function test_unpaid_order_status_is_pending()
    {
        $this->test_create_order_and_get_token();
        $response = $this->getJson('/api/order/' . $this->orderId);
        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'status' => OrderStatus::PENDING->value
        ]]);
    }

    public function test_pay_order($isOrderCreated = false)
    {
        if (!$isOrderCreated) {
            $this->test_create_order_and_get_token();
        }

        $response = $this->postJson('/api/order/' . $this->orderId . '/pay');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Payment successful']);
    }

    public function test_paid_order_status_is_confirmed()
    {
        $this->test_create_order_and_get_token();
        $this->test_pay_order(true);

        $response = $this->getJson('/api/order/' . $this->orderId);
        $response->assertStatus(200);
        $response->assertJson(['data' => ['status' => OrderStatus::CONFIRMED->value]]);
    }

    // Негативные кейсы:
    // - несуществующий токен
    // - несуществующий заказ
    public function test_nonexistent_order_without_headers()
    {
        $this->test_create_order_and_get_token();
        $response = $this->getJson('/api/order/999999');
        $response->assertStatus(401);
        $response->assertJson(['error' => true, 'message' => 'Token required']);
    }

    public function test_nonexistent_order_with_headers()
    {
        $this->test_create_order_and_get_token();
        $response = $this->getJson('/api/order/999999', [
            'X-Guest-Token' => $this->guestToken
        ]);
        $response->assertStatus(404);
        $response->assertJson(['error' => true, 'message' => 'Order not found']);
    }
}
