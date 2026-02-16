<?php

namespace App\Validation;

use App\Models\Room;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Support\DateHelper;
use Illuminate\Validation\Validator;

class ReservationEventsValidator
{
    public function validate(
        Validator $validator,
        Room $room,
        ?User $user,
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

        $availability = app(AvailabilityService::class);
        $availability->loadBusySlots($room);

        // Determine if user is admin (bypass for PAST, TOO_CLOSE, TOO_FAR, NON_BOOKABLE)
        $canManage = $user?->can('manageReservations', $room);

        // Load unavailabilities once for all events
        $unavailabilities = $room->unavailabilities;

        foreach ($parsedEvents as $i => $event) {

            // Check overlapping between events of the current reservation
            foreach ($parsedEvents as $j => $event2) {
                if ($i >= $j) {
                    continue; // Skip same event and already checked pairs
                }
                // Check overlap: start1 < end2 AND end1 > start2
                if ($event['start']->lt($event2['end']) && $event['end']->gt($event2['start'])) {
                    $validator->errors()->add('events', __('Reservation slots cannot overlap.'));

                    return; // Stop validation after first overlap found
                }
            }

            if (! $event['start']->lt($event['end'])) {
                $validator->errors()->add('events', __('Invalid reservation.'));
            }

            // cutoff (admin bypass)
            if ($room->reservation_cutoff_days && ! $canManage) {
                $min = now('UTC')
                    ->addDays($room->reservation_cutoff_days);

                if ($event['start']->lt($min)) {
                    $validator->errors()->add('events', __('Reservation too late.'));
                }
            }

            // advance limit (admin bypass)
            if ($room->reservation_advance_limit && ! $canManage) {
                $max = now('UTC')
                    ->addDays($room->reservation_advance_limit);

                if ($event['start']->gt($max)) {
                    $validator->errors()->add('events', __('Reservation too far in advance.'));
                }
            }

            // Convert to room timezone for bookable hours checks
            // (day_start_time, day_end_time, allowed_weekdays are defined in room TZ)
            $timezone = $room->getTimezone();
            $startInRoomTz = $event['start']->copy()->setTimezone($timezone);
            $endInRoomTz = $event['end']->copy()->setTimezone($timezone);

            // Check if event spans multiple days (in room timezone)
            $isMultiDay = ! $startInRoomTz->isSameDay($endInRoomTz);

            // Time range check - multi-day events are not allowed if time restrictions exist (admin bypass)
            if (($room->day_start_time || $room->day_end_time) && $isMultiDay && ! $canManage) {
                $validator->errors()->add('events', __('Multi-day bookings not allowed with time restrictions.'));
            }

            // Time range check for single-day events (admin bypass)
            if (($room->day_start_time || $room->day_end_time) && ! $isMultiDay && ! $canManage) {
                $startTime = $startInRoomTz->format('H:i');
                $endTime = $endInRoomTz->format('H:i');
                if ($room->day_start_time && $startTime < substr($room->day_start_time, 0, 5)) {
                    $validator->errors()->add('events', __('Booking starts too early.'));
                }
                if ($room->day_end_time && $endTime > substr($room->day_end_time, 0, 5)) {
                    $validator->errors()->add('events', __('Booking ends too late.'));
                }
            }

            // Weekday check - check ALL days between start and end (admin bypass)
            if (! $canManage) {
                $current = $startInRoomTz->copy()->startOfDay();
                $endDay = $endInRoomTz->copy()->startOfDay();

                while ($current->lte($endDay)) {
                    $eventDay = $current->dayOfWeekIso; // 1=Mon, 7=Sun
                    if (! in_array($eventDay, $room->allowed_weekdays)) {
                        $validator->errors()->add('events', __('Booking not allowed on this day.'));
                        break;
                    }
                    $current->addDay();
                }
            }

            // Custom unavailability check (admin bypass, use UTC accessors)
            if (! $canManage) {
                foreach ($unavailabilities as $unavailability) {
                    if ($event['start']->lt($unavailability->end()) && $event['end']->gt($unavailability->start())) {
                        $validator->errors()->add('events', __('Room unavailable during this period.'));
                        break;
                    }
                }
            }

            // availability (CalDAV/existing reservations - NOT bypassed for admin)
            if (! $availability->checkAvailability($room, $event['start'], $event['end'], $event['uid'])) {
                $validator->errors()->add('events', __('Room unavailable.'));
            }
        }
    }
}
