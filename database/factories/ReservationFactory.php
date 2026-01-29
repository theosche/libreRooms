<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Enums\ReservationStatus;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullPrice = rand(50,500);
        $discounts = fake()->randomFloat(2, 0, 100);
        $specialDiscount = fake()->optional(0.3)->randomFloat(2, 10, 50);
        $donation = fake()->optional(0.4)->randomFloat(2, 5, 50);
        $confirmed_at = fake()->optional(0.6)->dateTimeBetween('-30 days', 'now');

        return [
            'room_id' => Room::factory(),
            'tenant_id' => Contact::factory(),
            'hash' => Str::random(32),
            'status' => fake()->randomElement([ReservationStatus::PENDING, ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED,ReservationStatus::FINISHED]),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'full_price' => $fullPrice,
            'sum_discounts' => $discounts,
            'special_discount' => $specialDiscount,
            'donation' => $donation,
            'confirmed_at' => $confirmed_at,
            'confirmed_by' => $confirmed_at ? User::factory() : null,
            'cancelled_at' => fake()->optional(0.1)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the reservation is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'confirmed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the reservation is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the reservation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
            'confirmed_by' => null,
            'cancelled_at' => null,
        ]);
    }
}
