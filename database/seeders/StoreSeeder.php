<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            ['name' => 'Shopno - Dhaka', 'city' => 'Dhaka', 'address' => '123 Dhaka Street'],
            ['name' => 'Shopno - Chittagong', 'city' => 'Chittagong', 'address' => '456 Chittagong Road'],
            ['name' => 'Shopno - Sylhet', 'city' => 'Sylhet', 'address' => '789 Sylhet Avenue'],
            ['name' => 'Shopno - Khulna', 'city' => 'Khulna', 'address' => '101 Khulna Lane'],
            ['name' => 'Shopno - Rajshahi', 'city' => 'Rajshahi', 'address' => '202 Rajshahi Street'],
            ['name' => 'Shopno - Barishal', 'city' => 'Barishal', 'address' => '303 Barishal Road'],
            ['name' => 'Shopno - Rangpur', 'city' => 'Rangpur', 'address' => '404 Rangpur Avenue'],
            ['name' => 'Shopno - Mymensingh', 'city' => 'Mymensingh', 'address' => '505 Mymensingh Lane'],
        ];

        foreach ($stores as $store) {
            Store::create($store);
        }
    }
}
