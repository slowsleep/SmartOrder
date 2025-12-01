<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::withoutEvents(function () {
            collect([
                    'Пицца Маргарита', 'Пицца Пепперони', 'Пицца Гавайская',
                    'Бургер Классический', 'Бургер Чизбургер', 'Бургер Вегетарианский',
                    'Салат Цезарь', 'Салат Греческий', 'Салат Оливье',
                    'Суп Том Ям', 'Суп Борщ', 'Суп Грибной',
                    'Стейк Рибай', 'Стейк Нью-Йорк', 'Стейк Филе-миньон',
                    'Паста Карбонара', 'Паста Болоньезе', 'Паста Арабьята',
                    'Кофе Латте', 'Кофе Капучино', 'Кофе Американо',
                    'Чай Зеленый', 'Чай Черный', 'Чай Фруктовый'
            ])->each(function ($product) {
                Product::factory()->create(['name' => $product]);
            });
        });
    }
}
