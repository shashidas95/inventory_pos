<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // UserSeeder::class,
            // ProfileSeeder::class,
            // StoreSeeder::class,
            // CategorySeeder::class,
            // ProductSeeder::class,
            // OrderSeeder::class,
            // OrderDetailSeeder::class,
            // InvoiceSeeder::class,
            // InvoiceDetailSeeder::class
        ]);
    }
}
