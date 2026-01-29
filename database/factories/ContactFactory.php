<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\ContactTypes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([ContactTypes::INDIVIDUAL,ContactTypes::ORGANIZATION]);

        return [
            'type' => $type,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'entity_name' => $type === ContactTypes::ORGANIZATION ? fake()->company() : null,
            'email' => fake()->safeEmail(),
            'invoice_email' => fake()->optional()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'street' => fake()->streetAddress(),
            'zip' => fake()->postcode(),
            'city' => fake()->city(),
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContactTypes::INDIVIDUAL,
            'entity_name' => null,
        ]);
    }

    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContactTypes::ORGANIZATION,
            'entity_name' => fake()->company(),
        ]);
    }
}
