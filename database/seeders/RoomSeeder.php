<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\Room;
use App\Models\RoomDiscount;
use App\Models\RoomOption;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up images from previous seeds
        Storage::disk('public')->deleteDirectory('rooms');

        $seedImages = glob(database_path('seeders/data/room-images/*.jpg'));

        Owner::all()->each(function ($owner) use ($seedImages) {
            Room::factory()
                ->count(4)
                ->for($owner)
                ->create()
                ->each(function ($room) use ($seedImages) {
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

                    $this->attachRandomImages($room, $seedImages);
                });
        });
    }

    /**
     * Attach 1-3 random seed images to a room.
     *
     * @param array<string> $seedImages
     */
    private function attachRandomImages(Room $room, array $seedImages): void
    {
        if (empty($seedImages)) {
            return;
        }

        $selected = collect($seedImages)->shuffle()->take(rand(1, 3));

        $selected->each(function (string $sourcePath, int $index) use ($room) {
            $filename = basename($sourcePath);
            $storagePath = "rooms/{$room->id}/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));

            $room->images()->create([
                'path' => $storagePath,
                'original_name' => $filename,
                'order' => $index,
            ]);
        });
    }
}
