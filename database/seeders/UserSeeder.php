<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::withoutEvents(function () {
            collect([
                ['admin', 'admin12345'],
                ['cook', 'cook12345'],
                ['waiter', 'waiter12345']
            ])->each(function ($userData) {
                User::factory()->create([
                    'login' => $userData[0],
                    'password' => Hash::make($userData[1]),
                    'role_id' => Role::where('name', $userData[0])->first()->id
                ]);
            });
        });
    }
}
