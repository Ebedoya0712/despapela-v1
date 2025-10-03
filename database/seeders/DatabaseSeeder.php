<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llama a los seeders en el orden que necesites
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            CompanySeeder::class,
            TagSeeder::class,
        ]);
    }
}