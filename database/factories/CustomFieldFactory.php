<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\CustomFieldTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomField>
 */
class CustomFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [CustomFieldTypes::TEXT,CustomFieldTypes::TEXTAREA,CustomFieldTypes::CHECKBOX,CustomFieldTypes::RADIO,CustomFieldTypes::SELECT];
        $type = fake()->randomElement($types);

        return [
            'room_id' => Room::factory(),
            'key' => fake()->unique()->slug(2),
            'label' => fake()->words(3, true),
            'type' => $type,
            'options' => in_array($type, [CustomFieldTypes::CHECKBOX,CustomFieldTypes::RADIO,CustomFieldTypes::SELECT])
                ? fake()->randomElements(['Option 1', 'Option 2', 'Option 3', 'Option 4'], fake()->numberBetween(2, 4))
                : null,
            'required' => fake()->boolean(30),
            'active' => fake()->boolean(90),
        ];
    }
}
