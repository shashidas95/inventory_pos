<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id')->toArray();
        $faker = fake(); // Initialize faker for direct use

        // We are using a direct creation loop instead of Model::factory()
        // to avoid dependency issues if the HasFactory trait or ProductFactory file is missing.

        for ($i = 0; $i < 30; $i++) {
            // Generate a descriptive product name
            $name = $faker->unique()->words(3, true) . ' ' . $faker->randomElement(['Pro', 'Max', 'Light', 'Standard']);

            Product::create([
                'category_id' => $faker->randomElement($categoryIds),
                'name' => ucfirst($name),
                'description' => $faker->paragraph(),
                'price' => $faker->randomFloat(2, 5, 500),
                'quantity' => $faker->numberBetween(10, 200),
                'image' => null, // Placeholder
            ]);
        }
    }
}
