<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Table;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order_datetime = $this->faker->dateTimeBetween('-1 hour', 'now');

        $expires_at = clone $order_datetime;
        $expires_at->modify('+2 hour');

        $paid_at = clone $order_datetime;
        $paid_at->modify('+2 minutes');

        $status = $this->faker->randomElement(
                array_column(OrderStatus::cases(), 'value')
        );

        $is_pending = $status === OrderStatus::PENDING->value;

        return [
            'table_id' => Table::inRandomOrder()->first()->id,
            'status' => $status,
            'notes' => $this->faker->optional()->sentence(),
            'guest_token' => $this->faker->uuid(),
            'expires_at' => $expires_at->format('Y-m-d H:i:s'),
            'paid_at' =>  $is_pending ? null : $paid_at->format('Y-m-d H:i:s'),
            'created_at' => $order_datetime,
            'updated_at' => $order_datetime
        ];
    }
}
