<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Owner;
use App\Models\Reservation;
use App\Models\CustomFieldValue;
use App\Models\ReservationEvent;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use App\Enums\ReservationStatus;
use App\Enums\ContactTypes;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::all()->each(function ($owner) {
            $owner->rooms->each(function ($room) use ($owner) {
                Reservation::factory()
                    ->count(rand(5, 15))
                    ->for($room)
                    ->state(fn () => [
                        'tenant_id'  => Contact::inRandomOrder()->first()->id,
                    ])
                    ->create()
                    ->each(function ($reservation) use ($room, $owner) {
                        if ($room->discounts->isNotEmpty() && rand(0, 1)) {
                            $discounts = $room->discounts
                                ->random(rand(1, min(2, $room->discounts->count())));
                            $reservation->discounts()->attach($discounts);
                        }

                        if ($room->customFields->isNotEmpty()) {
                            $room->customFields->each(function ($customField) use ($reservation) {
                                CustomFieldValue::factory()->fromReservationAndField($reservation, $customField);
                            });
                        }

                        ReservationEvent::factory()
                            ->count(rand(1, 3))
                            ->for($reservation)
                            ->create()
                            ->each(function ($event) use ($room) {
                                if ($room->options->isNotEmpty() && rand(0, 1)) {
                                    $options = $room->options
                                        ->random(rand(1, min(3, $room->options->count())));

                                    foreach ($options as $option) {
                                        $event->options()->attach($option->id, [
                                            'price' => $option->price,
                                        ]);
                                    }
                                }
                            });

                        if ($reservation->status !== ReservationStatus::PENDING) {
                            $finalAmount = $reservation->full_price - $reservation->sum_discounts;
                            if ($reservation->special_discount) {
                                $finalAmount -= $reservation->special_discount;
                            }
                            if ($reservation->donation) {
                                $finalAmount += $reservation->donation;
                            }

                            Invoice::factory()
                                ->for($reservation)
                                ->for($owner)
                                ->create([
                                    'amount' => $finalAmount,
                                ]);
                        }
                    });
            });
        });
    }
}
