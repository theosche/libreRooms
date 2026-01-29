<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemSettingsSeeder::class,
            IdentityProviderSeeder::class,
            UserSeeder::class,
            OwnerSeeder::class,
            RoomSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
