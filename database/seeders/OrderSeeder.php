<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $storeIds = Store::pluck('id')->toArray();

        // Create 15 orders
        Order::factory(15)->create([
            'user_id' => function () use ($userIds) {
                return fake()->randomElement($userIds);
            },
            'store_id' => function () use ($storeIds) {
                return fake()->randomElement($storeIds);
            },
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total' => fake()->randomFloat(2, 50, 5000), // Total will be recalculated by OrderDetailSeeder
        ]);
    }
}
