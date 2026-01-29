<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 owners
        Owner::factory(5)
            ->create()
            ->each(function (Owner $owner) {
                $users = User::inRandomOrder()->limit(rand(3, 9))->get();

                $users->each(function (User $user) use ($owner) {
                    $owner->users()->attach($user->id, [
                        'role' => fake()->randomElement(['admin', 'moderator', 'viewer']),
                    ]);
                });
            });
    }
}
