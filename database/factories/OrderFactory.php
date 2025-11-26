<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Role;

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
        return [
            'table_number' => $this->faker->numberBetween(1, 20),
            'delivery_type' => $this->faker->randomElement(['full', 'partial']),
            'status' => $this->faker->randomElement([
                'pending',
                'confirmed',
                'preparing',
                'partially_ready',
                'ready',
                'completed',
                'cancelled'
            ]),
            'notes' => $this->faker->optional()->sentence(),
            'waiter_id' => User::where('role', Role::where('name', 'waiter')->first()->id)
                ->inRandomOrder()
                ->first()
                ->id,
        ];
    }
}
