<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Enums\OrderItemStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(
                array_column(OrderItemStatus::cases(), 'value')
        );

        $is_pending = $status === OrderItemStatus::PENDING->value;
        $is_in_delivery = $status === OrderItemStatus::IN_DELIVERY->value;
        $is_served = $status === OrderItemStatus::SERVED->value;
        $is_cancelled = $status === OrderItemStatus::CANCELLED->value;

        return [
            'product_id' => Product::inRandomOrder()->first()->id,
            'unit_price' => $this->faker->randomFloat(2, 5, 500),
            'status' => $status,
            'cook_id' => !($is_pending || $is_cancelled)
                ? User::where('role_id', Role::class::where('name', 'cook')->first()->id)
                    ->inRandomOrder()
                    ->first()
                    ->id
                : null,
            'waiter_id' => ($is_in_delivery || $is_served)
                ? User::where('role_id', Role::class::where('name', 'waiter')->first()->id)
                    ->inRandomOrder()
                    ->first()
                    ->id
                : null,
            'served_at' => $is_served ? $this->faker->dateTimeBetween('-2 days', 'now') : null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
