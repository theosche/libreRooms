<?php

namespace Database\Factories;

use App\Enums\CustomFieldTypes;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomFieldValue>
 */
class CustomFieldValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        ];
    }
    public function fromReservationAndField(
        Reservation $reservation,
        CustomField $customField
    ): CustomFieldValue {
        $value = match ($customField->type) {
            CustomFieldTypes::CHECKBOX => fake()->randomElements(
                array_keys($customField->options ?? []),
                rand(1, min(3, count($customField->options ?? []))),
            ),
            CustomFieldTypes::RADIO,
            CustomFieldTypes::SELECT => fake()->randomElement(
                array_keys($customField->options ?? [])
            ),
            default => fake()->sentence(),
        };

        return CustomFieldValue::fromReservationAndField(
            $reservation,
            $customField,
            $value
        );
    }
}
