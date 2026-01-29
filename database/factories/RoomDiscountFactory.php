<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\ContactTypes;
use App\Enums\DiscountTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomDiscount>
 */
class RoomDiscountFactory extends Factory
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
            'name' => fake()->randomElement(['Tarif étudiant', 'Tarif réduit', 'Tarif senior', 'Early bird', 'Groupe']),
            'description' => fake()->realText(60),
            'type' => fake()->randomElement([DiscountTypes::FIXED, DiscountTypes::PERCENT]),
            'limit_to_contact_type' => fake()->optional()->randomElement([ContactTypes::INDIVIDUAL,ContactTypes::ORGANIZATION]),
            'value' => rand(1,5)*10,
            'active' => fake()->boolean(90),
        ];
    }
}
