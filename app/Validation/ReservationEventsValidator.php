<?php

namespace App\Validation;
use Illuminate\Validation\Validator;
use App\Models\Room;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Support\DateHelper;

class ReservationEventsValidator
{
    public function validate(
        Validator $validator,
        Room $room,
        User|null $user,
        array $events,
    ): void {
        // Check for overlapping events within the same reservation
        $parsedEvents = [];
        foreach ($events as $index => $event) {
            $parsedEvents[$index] = [
                'start' => DateHelper::fromLocalInput($event['start'], $room->getTimezone()),
                'end' => DateHelper::fromLocalInput($event['end'], $room->getTimezone()),
                'uid' => $event['uid'] ?? null,
            ];
        }

        foreach ($parsedEvents as $i => $ev1) {

        }

        $availability = app(AvailabilityService::class);
        $availability->loadBusySlots($room);
        foreach ($parsedEvents as $i => $event) {

            // Check overlapping between events of the current reservation
            foreach ($parsedEvents as $j => $event2) {
                if ($i >= $j) {
                    continue; // Skip same event and already checked pairs
                }
                // Check overlap: start1 < end2 AND end1 > start2
                if ($event['start']->lt($event2['end']) && $event['end']->gt($event2['start'])) {
                    $validator->errors()->add('events', __('Les créneaux de réservation ne peuvent pas se chevaucher.'));
                    return; // Stop validation after first overlap found
                }
            }

            if (!$event['start']->lt($event['end'])) {
                $validator->errors()->add('events', __('Réservation invalide.'));
            }

            // cutoff
            if ($room->reservation_cutoff_days) {
                $isAdmin = $user?->isAdminOf($room->owner);
                if (! $isAdmin) {
                    $min = now('UTC')
                        ->addDays($room->reservation_cutoff_days);

                    if ($event['start']->lt($min)) {
                        $validator->errors()->add('events', __('Réservation trop tardive.'));
                    }
                }
            }

            // advance limit
            if ($room->reservation_advance_limit) {
                $max = now('UTC')
                    ->addDays($room->reservation_advance_limit);

                if ($event['start']->gt($max)) {
                    $validator->errors()->add('events', __('Réservation trop anticipée.'));
                }
            }

            // availability
            if (! $availability->checkAvailability($room, $event['start'], $event['end'], $event['uid'])) {
                $validator->errors()->add('events', __('Salle indisponible.'));
            }
        }
    }

}
