<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationEvent>
 */
class ReservationEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = fake()->randomElement([0,1,2,3,10,15,20,30,40,50,60,70,80,90,100]);
        $startAt = fake()->dateTimeBetween('now', "+{$days} days");

        $duration = fake()->randomElement([2, 3, 4, 6, 8, 10, 24]);
        $endAt = (clone $startAt)->modify("+{$duration} hours");

        return [
            'reservation_id' => Reservation::factory(),
            'start' => $startAt,
            'end' => $endAt,
            'uid' => fake()->numerify('##################'),
            'price' => rand(50, 200),
            'price_label' => fake()->sentence(),
        ];
    }
}
