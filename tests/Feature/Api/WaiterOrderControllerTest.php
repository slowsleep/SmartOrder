<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Table;
use App\Enums\OrderItemStatus;
use Laravel\Sanctum\Sanctum;

/**
 * Tests for Api/WaiterOrderController
 */
class WaiterOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $waiter;
    protected User $cook;
    protected OrderItem $readyOrderItem;
    protected OrderItem $inDeliveryOrderItem;
    protected OrderItem $servedOrderItem;
    protected OrderItem $preparingOrderItem;
    protected OrderItem $pendingOrderItem;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем роли
        $waiterRole = Role::create(['name' => 'waiter', 'description' => 'Официант']);
        $cookRole = Role::create(['name' => 'cook', 'description' => 'Повар']);

        // Создаем пользователей
        User::withoutEvents(function () use ($waiterRole, $cookRole) {
            $this->waiter = User::factory()->create(['role_id' => $waiterRole->id]);
            $this->cook = User::factory()->create(['role_id' => $cookRole->id]);
        });

        // Создаем стол
        $table = Table::factory()->create();

        // Создаем продукт
        $product = Product::withoutEvents(function () {
            return Product::factory()->create();
        });

        // Создаем заказ
        $order = Order::factory()->create([
            'table_id' => $table->id,
        ]);

        // Создаем OrderItem'ы с разными статусами
        OrderItem::withoutEvents(function () use ($order, $product) {
            $this->readyOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::READY->value,
                'cook_id' => $this->cook->id,
                'waiter_id' => null,
            ]);

            $this->inDeliveryOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::IN_DELIVERY->value,
                'cook_id' => $this->cook->id,
                'waiter_id' => $this->waiter->id,
            ]);

            $this->servedOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::SERVED->value,
                'cook_id' => $this->cook->id,
                'waiter_id' => $this->waiter->id,
            ]);

            $this->preparingOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::PREPARING->value,
                'cook_id' => $this->cook->id,
                'waiter_id' => null,
            ]);

            $this->pendingOrderItem = OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'status' => OrderItemStatus::PENDING->value,
                'cook_id' => null,
                'waiter_id' => null,
            ]);
        });
    }

    public function test_unauthenticated_user_cannot_access_waiter_endpoints(): void
    {
        $response = $this->getJson('/api/staff/waiter/order');
        $response->assertStatus(401);

        $response = $this->postJson('/api/staff/waiter/order/1/get');
        $response->assertStatus(401);

        $response = $this->postJson('/api/staff/waiter/order/1/served');
        $response->assertStatus(401);
    }

    public function test_non_waiter_user_cannot_access_waiter_endpoints(): void
    {
        Sanctum::actingAs($this->cook);

        $response = $this->getJson('/api/staff/waiter/order');
        $response->assertStatus(403);

        $response = $this->postJson("/api/staff/waiter/order/{$this->readyOrderItem->id}/get");
        $response->assertStatus(403);

        $response = $this->postJson("/api/staff/waiter/order/{$this->inDeliveryOrderItem->id}/served");
        $response->assertStatus(403);
    }

    public function test_waiter_can_view_ready_order_items_queue(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->getJson('/api/staff/waiter/order');

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
                        'cook_id',
                        'waiter_id',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);

        // Проверяем что вернулись только READY статусы
        $data = $response->json('data');
        $this->assertCount(1, $data); // только readyOrderItem
        $this->assertEquals(OrderItemStatus::READY->value, $data[0]['status']);
        $this->assertNull($data[0]['waiter_id']); // еще не взяты официантом
    }

    public function test_waiter_can_view_own_order_items(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->getJson('/api/staff/waiter/order/owns');

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

        // Проверяем что вернулся только IN_DELIVERY статус для данного официанта
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(OrderItemStatus::IN_DELIVERY->value, $data[0]['status']);
        $this->assertEquals($this->waiter->id, $data[0]['waiter_id']);
        $this->assertEquals($this->inDeliveryOrderItem->id, $data[0]['id']);
    }

    public function test_waiter_can_take_ready_order_item_for_delivery(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->postJson("/api/staff/waiter/order/{$this->readyOrderItem->id}/get");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Order item taken',
                'data' => []
            ]);

        // Проверяем изменения в базе
        $this->readyOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::IN_DELIVERY->value, $this->readyOrderItem->status);
        $this->assertEquals($this->waiter->id, $this->readyOrderItem->waiter_id);
    }

    public function test_waiter_cannot_take_non_ready_order_item(): void
    {
        Sanctum::actingAs($this->waiter);

        // Пытаемся взять уже взятый в доставку item
        $response = $this->postJson("/api/staff/waiter/order/{$this->inDeliveryOrderItem->id}/get");
        $response->assertStatus(404);

        // Пытаемся взять уже поданный item
        $response = $this->postJson("/api/staff/waiter/order/{$this->servedOrderItem->id}/get");
        $response->assertStatus(404);

        // Пытаемся взять готовящийся item
        $response = $this->postJson("/api/staff/waiter/order/{$this->preparingOrderItem->id}/get");
        $response->assertStatus(404);

        // Пытаемся взять ожидающий item
        $response = $this->postJson("/api/staff/waiter/order/{$this->pendingOrderItem->id}/get");
        $response->assertStatus(404);
    }

    public function test_waiter_cannot_take_non_existent_order_item(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->postJson('/api/staff/waiter/order/999999/get');
        $response->assertStatus(404);
    }

    public function test_waiter_can_mark_his_in_delivery_item_as_served(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->postJson("/api/staff/waiter/order/{$this->inDeliveryOrderItem->id}/served");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Order item served',
                'data' => []
            ]);

        // Проверяем изменения в базе
        $this->inDeliveryOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::SERVED->value, $this->inDeliveryOrderItem->status);
        // waiter_id официанта, что взял item
        $this->assertEquals($this->waiter->id, $this->inDeliveryOrderItem->waiter_id);
    }

    public function test_waiter_cannot_mark_non_in_delivery_item_as_served(): void
    {
        Sanctum::actingAs($this->waiter);

        // Пытаемся отметить поданным READY item
        $response = $this->postJson("/api/staff/waiter/order/{$this->readyOrderItem->id}/served");
        $response->assertStatus(404);

        // Пытаемся отметить поданным уже SERVED item
        $response = $this->postJson("/api/staff/waiter/order/{$this->servedOrderItem->id}/served");
        $response->assertStatus(404);

        // Пытаемся отметить поданным PREPARING item
        $response = $this->postJson("/api/staff/waiter/order/{$this->preparingOrderItem->id}/served");
        $response->assertStatus(404);

        // Пытаемся отметить поданным PENDING item
        $response = $this->postJson("/api/staff/waiter/order/{$this->pendingOrderItem->id}/served");
        $response->assertStatus(404);
    }

    public function test_waiter_cannot_take_item_already_taken_by_another_waiter(): void
    {
        // Создаем другого официанта
        $waiterRole = Role::where('name', 'waiter')->first();
        $anotherWaiter = User::withoutEvents(function () use ($waiterRole) {
            return User::factory()->create(['role_id' => $waiterRole->id]);
        });

        $order = Order::factory()->create();

        // Создаем OrderItem, уже взятый другим официантом
        $takenItem = OrderItem::withoutEvents(function () use ($order, $anotherWaiter) {
            return OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::IN_DELIVERY->value,
                'waiter_id' => $anotherWaiter->id,
            ]);
        });

        Sanctum::actingAs($this->waiter);

        // Пытаемся взять уже взятый item
        $response = $this->postJson("/api/staff/waiter/order/{$takenItem->id}/get");
        $response->assertStatus(404); // не найдет, т.к. статус уже IN_DELIVERY
    }

    public function test_waiter_cannot_mark_another_waiters_item_as_served(): void
    {
        // Создаем другого официанта
        $waiterRole = Role::where('name', 'waiter')->first();
        $anotherWaiter = User::withoutEvents(function () use ($waiterRole) {
            return User::factory()->create(['role_id' => $waiterRole->id]);
        });

        $anotherOrder = Order::factory()->create();

        // Создаем OrderItem, который взял другой официант
        $anotherWaiterItem = OrderItem::withoutEvents(function () use ($anotherWaiter, $anotherOrder) {
            return OrderItem::factory()->create([
                'order_id' => $anotherOrder->id,
                'status' => OrderItemStatus::IN_DELIVERY->value,
                'waiter_id' => $anotherWaiter->id,
            ]);
        });

        Sanctum::actingAs($this->waiter);

        // Текущий официант пытается отметить поданным блюдо другого официанта
        $response = $this->postJson("/api/staff/waiter/order/{$anotherWaiterItem->id}/served");
        $response->assertStatus(404); // не найдет, т.к. where('waiter_id', Auth::id())
    }

    public function test_waiter_cannot_mark_non_existent_item_as_served(): void
    {
        Sanctum::actingAs($this->waiter);

        $response = $this->postJson('/api/staff/waiter/order/999999/served');
        $response->assertStatus(404);
    }

    public function test_order_item_delivery_flow_is_correct(): void
    {
        Sanctum::actingAs($this->waiter);

        // Проверяем что item в READY
        $this->assertEquals(OrderItemStatus::READY->value, $this->readyOrderItem->status);
        $this->assertNull($this->readyOrderItem->waiter_id);
        $this->assertNull($this->readyOrderItem->served_at);

        // Берем в доставку
        $response = $this->postJson("/api/staff/waiter/order/{$this->readyOrderItem->id}/get");
        $response->assertStatus(200);

        $this->readyOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::IN_DELIVERY->value, $this->readyOrderItem->status);
        $this->assertEquals($this->waiter->id, $this->readyOrderItem->waiter_id);
        $this->assertNull($this->readyOrderItem->served_at);

        // Отмечаем как поданное
        $response = $this->postJson("/api/staff/waiter/order/{$this->readyOrderItem->id}/served");
        $response->assertStatus(200);

        $this->readyOrderItem->refresh();
        $this->assertEquals(OrderItemStatus::SERVED->value, $this->readyOrderItem->status);
        $this->assertEquals($this->waiter->id, $this->readyOrderItem->waiter_id);
        $this->assertNotNull($this->readyOrderItem->served_at);
    }

    public function test_queue_shows_only_ready_items(): void
    {
        $order = Order::factory()->create();

        // Создаем дополнительные READY items
        $readyItem1 = OrderItem::withoutEvents(function () use ($order) {
            return OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::READY->value,
                'waiter_id' => null,
            ]);
        });

        $readyItem2 = OrderItem::withoutEvents(function () use ($order) {
            return OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::READY->value,
                'waiter_id' => null,
            ]);
        });

        // И другие статусы, которые не должны показываться
        OrderItem::withoutEvents(function () use ($order) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'status' => OrderItemStatus::IN_DELIVERY->value,
                'waiter_id' => $this->waiter->id,
            ]);
        });

        Sanctum::actingAs($this->waiter);

        $response = $this->getJson('/api/staff/waiter/order');
        $response->assertStatus(200);

        $data = $response->json('data');

        // Должно быть 3 READY items (2 новых + readyOrderItem из setUp)
        $this->assertCount(3, $data);

        // Проверяем что все имеют статус READY
        foreach ($data as $item) {
            $this->assertEquals(OrderItemStatus::READY->value, $item['status']);
            $this->assertNull($item['waiter_id']); // еще не взяты
        }
    }
}
