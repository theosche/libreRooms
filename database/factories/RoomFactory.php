<?php

namespace Database\Factories;

use App\Enums\CalendarViewModes;
use App\Enums\CharterModes;
use App\Enums\EmbedCalendarModes;
use App\Enums\ExternalSlotProviders;
use App\Enums\PriceModes;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Salle '.fake()->unique()->company();
        $price_short = fake()->optional(0.75)->numberBetween(10, 50);

        return [
            'owner_id' => Owner::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => 'Suisse',
            'latitude' => fake()->latitude(45.8, 47.8),
            'longitude' => fake()->longitude(5.9, 10.5),
            'active' => fake()->boolean(90),
            'is_public' => fake()->boolean(60),
            'price_mode' => fake()->randomElement([PriceModes::FIXED, PriceModes::FREE]),
            'price_short' => $price_short,
            'price_full_day' => rand(50, 200),
            'max_hours_short' => $price_short ? fake()->numberBetween(3, 6) : null,
            'always_short_after' => $price_short ? fake()->optional(0.75)->numberBetween(16, 20) : null,
            'always_short_before' => $price_short ? fake()->optional(0.75)->numberBetween(10, 13) : null,
            'allow_late_end_hour' => fake()->numberBetween(0, 7),
            'reservation_cutoff_days' => fake()->optional()->numberBetween(1, 7),
            'reservation_advance_limit' => fake()->optional()->numberBetween(90, 700),
            'allowed_weekdays' => fake()->boolean(50)
                ? collect(range(1, 7))->random(fake()->numberBetween(1, 7))->sort()->values()->toArray()
                : null,
            'day_start_time' => fn (array $attributes) => $attributes['allowed_weekdays']
                ? sprintf('%02d:00', fake()->numberBetween(5, 9))
                : null,
            'day_end_time' => fn (array $attributes) => $attributes['allowed_weekdays']
                ? sprintf('%02d:00', fake()->numberBetween(20, 23))
                : null,
            'use_special_discount' => fake()->boolean(30),
            'use_donation' => fake()->boolean(30),
            'charter_mode' => fake()->randomElement([CharterModes::TEXT, CharterModes::LINK, CharterModes::NONE]),
            'charter_str' => fake()->paragraph(),
            'custom_message' => fake()->optional()->sentence(),
            'secret_message' => fake()->optional()->sentence(),
            'external_slot_provider' => fake()->optional(0.5)->randomElement([ExternalSlotProviders::CALDAV]),
            'dav_calendar' => 'reservation_test-1',
            'embed_calendar_mode' => fake()->randomElement([EmbedCalendarModes::ENABLED, EmbedCalendarModes::DISABLED, EmbedCalendarModes::ADMIN_ONLY]),
            'calendar_view_mode' => fake()->randomElement([CalendarViewModes::FULL, CalendarViewModes::TITLE, CalendarViewModes::SLOT]),
            'timezone' => 'Europe/Zurich',
            'disable_mailer' => false,
        ];
    }
}
