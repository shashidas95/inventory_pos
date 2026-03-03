<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily for bulk insertion safety
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Ensure the profiles table is empty before seeding
        Profile::truncate();

        // Retrieve all existing user IDs
        $userIds = User::pluck('id')->toArray();
        $faker = fake();

        $profilesToCreate = [];

        foreach ($userIds as $userId) {
            $profilesToCreate[] = [
                'user_id' => $userId,
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'avatar' => 'https://placehold.co/150x150/000000/FFFFFF/png?text=' . substr(str_replace(' ', '+', $faker->firstName()), 0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all profiles at once for better performance
        Profile::insert($profilesToCreate);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
