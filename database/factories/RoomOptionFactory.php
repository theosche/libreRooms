<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomOption>
 */
class RoomOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'name' => fake()->randomElement(['Vidéoprojecteur', 'Sono', 'Catering', 'Chaises supplémentaires', 'Tables supplémentaires']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 100),
            'active' => fake()->boolean(90),
        ];
    }
}
