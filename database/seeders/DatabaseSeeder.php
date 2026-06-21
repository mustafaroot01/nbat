<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GovernorateSeeder::class,
            AdminSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            PlantSeeder::class,
        ]);
    }
}
