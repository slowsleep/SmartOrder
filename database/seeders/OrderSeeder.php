<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::withoutEvents(function(){
            Order::factory()
                ->count(10)
                ->hasItems(3)
                ->create()
                ->each(function (Order $order) {
                    // Обновляем время создания для всех связанных OrderItem
                    $order->items()->each(function (OrderItem $item) use ($order) {
                        $itemCreatedAt = Carbon::parse($order->created_at);

                        $item->update([
                            'created_at' => $itemCreatedAt,
                            'updated_at' => $itemCreatedAt,
                            'served_at' => $item->served_at
                                ? $itemCreatedAt->copy()->addMinutes(rand(5, 20))
                                : null,
                        ]);
                    });
                });
        });
    }
}
