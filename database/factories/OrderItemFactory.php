<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;

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
        return [
            'product_id' => Product::inRandomOrder()->first()->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $this->faker->randomFloat(2, 5, 500),
            'status' => $this->faker->randomElement([
                'pending',
                'preparing',
                'ready',
                'served',
                'cancelled'
            ]),
            'cook_id' => User::where('role', Role::class::where('name', 'cook')->first()->id)
                ->inRandomOrder()
                ->first()
                ->id,
            'served_by' => User::where('role', Role::class::where('name', 'waiter')->first()->id)
                ->inRandomOrder()
                ->first()
                ->id,
            'served_at' => $this->faker->optional()->dateTimeBetween('-2 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
