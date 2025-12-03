<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;


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
                ->create();
        });
    }
}
