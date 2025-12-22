<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Enums\OrderItemStatus;
use Laravel\Sanctum\Sanctum;
use App\Models\Table;

/**
 * Test for Api/CoorOrderController
 */
class CookOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $cook;
    protected User $waiter;
    protected OrderItem $pendingOrderItem;
    protected OrderItem $preparingOrderItem;
    protected OrderItem $readyOrderItem;
    protected OrderItem $cancelledOrderItem;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем роли
        $cookRole = Role::create(['name' => 'cook', 'description' => 'Повар']);
        $waiterRole = Role::create(['name' => 'waiter', 'description' => 'Официант']);

        // Создаем пользователей
        User::withoutEvents(function () use ($cookRole, $waiterRole) {
            $this->cook = User::factory()->create(['role_id' => $cookRole->id]);
            $this->waiter = User::factory()->create(['role_id' => $waiterRole->id]);
        });

        // Создаем стол
        $table = Table::factory()->create();

        // Создаем продукты
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        // Создаем заказ
        $order = Order::factory()->create([
            'table_id' => $table->id,
        ]);

        // Создаем OrderItem'ы с разными статусами
        OrderItem::withoutEvents(function () use ($order, $product) {
            $this->pendingOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::PENDING->value,
                'cook_id' => null,
            ]);

            $this->preparingOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::PREPARING->value,
                'cook_id' => $this->cook->id,
            ]);

            $this->readyOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::READY->value,
                'cook_id' => $this->cook->id,
            ]);

            $this->cancelledOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::CANCELLED->value,
                'cook_id' => null,
            ]);
        });
    }

    public function test_unauthenticated_user_cannot_access_cook_endpoints(): void
    {
        // Попытка доступа без аутентификации
        $response = $this->getJson('/api/staff/cook/order');
        $response->assertStatus(401);

        $response = $this->postJson('/api/staff/cook/order/1/take');
        $response->assertStatus(401);

        $response = $this->postJson('/api/staff/cook/order/1/ready');
        $response->assertStatus(401);
    }

    public function test_non_cook_user_cannot_access_cook_endpoints(): void
    {
        // Авторизуемся как официант
        Sanctum::actingAs($this->waiter);

        $response = $this->getJson('/api/staff/cook/order');
        $response->assertStatus(403);

        $response = $this->postJson("/api/staff/cook/order/{$this->pendingOrderItem->id}/take");
        $response->assertStatus(403);

        $response = $this->postJson("/api/staff/cook/order/{$this->preparingOrderItem->id}/ready");
        $response->assertStatus(403);
    }

    public function test_cook_can_view_pending_order_items_queue(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->getJson('/api/staff/cook/order');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'order_id',
                        'product_id',
                        'status',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);

        // Проверяем что вернулись только PENDING статусы
        $data = $response->json('data');
        $this->assertCount(1, $data); // только pendingOrderItem
        $this->assertEquals(OrderItemStatus::PENDING->value, $data[0]['status']);
    }

    public function test_cook_can_view_own_order_items(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->getJson('/api/staff/cook/order/own');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'order_id',
                        'product_id',
                        'unit_price',
                        'status',
                        'cook_id',
                        'waiter_id',
                        'served_at',
                        'notes',
                        'created_at',
                        'updated_at',
                        'product' => [
                            'id',
                            'name',
                            'description',
                            'is_active',
                            'price',
                            'quantity',
                            'image',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);

        // Проверяем что вернулся только PREPARING статус для данного повара
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(OrderItemStatus::PREPARING->value, $data[0]['status']);
        $this->assertEquals($this->cook->id, $data[0]['cook_id']);
        $this->assertEquals($this->preparingOrderItem->id, $data[0]['id']);
    }

    public function test_cook_can_take_pending_order_item_for_preparation(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->postJson("/api/staff/cook/order/{$this->pendingOrderItem->id}/take");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Order item taken',
                'data' => []
            ]);

        // Проверяем изменения в базе
        $this->pendingOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::PREPARING->value, $this->pendingOrderItem->status);
        $this->assertEquals($this->cook->id, $this->pendingOrderItem->cook_id);
    }

    public function test_cook_cannot_take_non_pending_order_item(): void
    {
        Sanctum::actingAs($this->cook);

        // Пытаемся взять уже готовый item
        $response = $this->postJson("/api/staff/cook/order/{$this->readyOrderItem->id}/take");
        $response->assertStatus(404);

        // Пытаемся взять отмененный item
        $response = $this->postJson("/api/staff/cook/order/{$this->cancelledOrderItem->id}/take");
        $response->assertStatus(404);

        // Пытаемся взять уже готовящийся item
        $response = $this->postJson("/api/staff/cook/order/{$this->preparingOrderItem->id}/take");
        $response->assertStatus(404);
    }

    public function test_cook_cannot_take_non_existent_order_item(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->postJson('/api/staff/cook/order/999999/take');
        $response->assertStatus(404);
    }

    public function test_cook_can_mark_his_preparing_item_as_ready(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->postJson("/api/staff/cook/order/{$this->preparingOrderItem->id}/ready");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Order item ready',
                'data' => []
            ]);

        // Проверяем изменения в базе
        $this->preparingOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::READY->value, $this->preparingOrderItem->status);
        // cook_id должен остаться прежним
        $this->assertEquals($this->cook->id, $this->preparingOrderItem->cook_id);
    }

    public function test_cook_cannot_mark_non_preparing_item_as_ready(): void
    {
        Sanctum::actingAs($this->cook);

        // Пытаемся отметить готовым PENDING item
        $response = $this->postJson("/api/staff/cook/order/{$this->pendingOrderItem->id}/ready");
        $response->assertStatus(404);

        // Пытаемся отметить готовым уже READY item
        $response = $this->postJson("/api/staff/cook/order/{$this->readyOrderItem->id}/ready");
        $response->assertStatus(404);

        // Пытаемся отметить готовым CANCELLED item
        $response = $this->postJson("/api/staff/cook/order/{$this->cancelledOrderItem->id}/ready");
        $response->assertStatus(404);
    }

    public function test_cook_cannot_mark_another_cooks_item_as_ready(): void
    {
        // Создаем другого повара
        $cookRole = Role::where('name', 'cook')->first();
        $anotherCook = User::withoutEvents(function () use ($cookRole) {
            return User::factory()->create(['role_id' => $cookRole->id]);
        });

        $anotherOrder = Order::factory()->create();

        // Создаем OrderItem, который готовит другой повар
        $anotherCookItem = OrderItem::withoutEvents(function () use ($anotherCook, $anotherOrder) {
            return OrderItem::factory()->create([
                'order_id' => $anotherOrder->id,
                'status' => OrderItemStatus::PREPARING->value,
                'cook_id' => $anotherCook->id,
            ]);
        });

        Sanctum::actingAs($this->cook);

        // Текущий повар пытается отметить готовым блюдо другого повара
        $response = $this->postJson("/api/staff/cook/order/{$anotherCookItem->id}/ready");
        $response->assertStatus(404); // не найдет, т.к. where('cook_id', Auth::id())
    }

    public function test_cook_cannot_mark_non_existent_item_as_ready(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->postJson('/api/staff/cook/order/999999/ready');
        $response->assertStatus(404);
    }

    public function test_order_item_status_flow_is_correct(): void
    {
        Sanctum::actingAs($this->cook);

        // 1. Проверяем что item в PENDING
        $this->assertEquals(OrderItemStatus::PENDING->value, $this->pendingOrderItem->status);
        $this->assertNull($this->pendingOrderItem->cook_id);

        // 2. Берем в работу
        $response = $this->postJson("/api/staff/cook/order/{$this->pendingOrderItem->id}/take");
        $response->assertStatus(200);

        $this->pendingOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::PREPARING->value, $this->pendingOrderItem->status);
        $this->assertEquals($this->cook->id, $this->pendingOrderItem->cook_id);

        // 3. Отмечаем как готовое
        $response = $this->postJson("/api/staff/cook/order/{$this->pendingOrderItem->id}/ready");
        $response->assertStatus(200);

        $this->pendingOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::READY->value, $this->pendingOrderItem->status);
        $this->assertEquals($this->cook->id, $this->pendingOrderItem->cook_id);
    }

    public function test_queue_shows_only_pending_items_sorted_by_created_at(): void
    {
        $order = Order::factory()->create();

        // Создаем дополнительные PENDING items с разным временем создания
        $olderItem = OrderItem::withoutEvents(function () use ($order) {
            return OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::PENDING->value,
                'created_at' => now()->subHour(),
            ]);
        });

        $newerItem = OrderItem::withoutEvents(function () use ($order) {
            return OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::PENDING->value,
                'created_at' => now(),
            ]);
        });

        Sanctum::actingAs($this->cook);

        $response = $this->getJson('/api/staff/cook/order');
        $response->assertStatus(200);

        $data = $response->json('data');

        // Должно быть 3 PENDING items (старый + 2 новых из setUp)
        $this->assertCount(3, $data);

        // Проверяем что первый в списке самый старый (сортировка по умолчанию)
        $this->assertEquals($olderItem->id, $data[0]['id']);
    }
}
