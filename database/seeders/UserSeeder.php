<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $storeIds = Store::pluck('id')->toArray();
        $numberOfStores = count($storeIds);

        // 1. Create a primary Admin user (no store affiliation)
        User::create([
            'name' => 'staff user',
            'email' => 'staff@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Use 'password' for easy testing
            'remember_token' => Str::random(10),
            'store_id' => null, // No store assigned to the main admin
        ]);

        // 2. Create a Manager user assigned to the first store
        User::create([
            'name' => 'Store Manager',
            'email' => 'skd.bsti@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'store_id' => $numberOfStores > 0 ? $storeIds[0] : null,
        ]);

        // 3. Create 8 more fake users, randomly assigning them to a store or leaving store_id null
        User::factory(8)->create([
            'store_id' => function () use ($storeIds) {
                // 50% chance of assigning a user to a random store, 50% chance of being null
                return (rand(0, 1) === 1 && !empty($storeIds)) ? fake()->randomElement($storeIds) : null;
            }
        ]);
    }
}
