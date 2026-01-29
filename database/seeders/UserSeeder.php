<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\ContactTypes;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = Contact::factory()->count(50)->create();
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@system.local',
            'password' => Hash::make('password'),
            'is_global_admin' => true,
            'email_verified_at' => now(),
        ]);
        $admin->contacts()->attach($contacts->random(rand(4,min(7, $contacts->count()))));
        User::factory()->count(15)->create()
            ->each(function ($user) use ($contacts) {
                $user->contacts()->attach($contacts->random(rand(0,min(2, $contacts->count()))));
            });
    }
}
