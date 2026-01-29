<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\Room;
use App\Models\RoomDiscount;
use App\Models\RoomOption;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::all()->each(function ($owner) {
            Room::factory()
                ->count(3)
                ->for($owner)
                ->create()
                ->each(function ($room) {
                    RoomDiscount::factory()
                        ->count(rand(0, 3))
                        ->create(['room_id' => $room->id]);

                    RoomOption::factory()
                        ->count(rand(0, 3))
                        ->create(['room_id' => $room->id]);

                    CustomField::factory()
                        ->count(rand(0, 3))
                        ->create(['room_id' => $room->id]);

                    $users = User::inRandomOrder()->limit(rand(3, 9))->get();
                    $users->each(function (User $user) use ($room) {
                        $room->users()->attach($user->id, [
                            'role' => 'viewer',
                        ]);
                    });
                });
        });
        $owner = Owner::first();
        $room = Room::factory()
            ->count(1)
            ->for($owner)
            ->create(['name' => 'test', 'slug' => 'test']);
        RoomDiscount::factory()
            ->count(5)
            ->create(['room_id' => $room[0]->id]);
        RoomOption::factory()
            ->count(4)
            ->create(['room_id' => $room[0]->id]);
        CustomField::factory()
            ->count(8)
            ->create(['room_id' => $room[0]->id]);
    }
}
