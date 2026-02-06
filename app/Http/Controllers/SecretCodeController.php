<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\View\View;

class SecretCodeController extends Controller
{
    /**
     * Display the secret codes for a reservation.
     * Public access via hash (obfuscated URL).
     */
    public function show(string $hash): View
    {
        $reservation = Reservation::where('hash', $hash)
            ->with(['room.owner', 'tenant', 'events'])
            ->firstOrFail();

        if (empty($reservation->room->secret_message)) {
            abort(404, __('No access codes available for this room.'));
        }

        $room = $reservation->room;
        $canView = false;
        $message = null;
        $availableFrom = null;

        if ($reservation->status === ReservationStatus::PENDING) {
            $message = __('This reservation is pending confirmation. Access codes will be available once the reservation is confirmed.');
        } elseif ($reservation->status === ReservationStatus::CANCELLED) {
            $message = __('This reservation has been cancelled.');
        } elseif (in_array($reservation->status, [ReservationStatus::CONFIRMED, ReservationStatus::FINISHED], true)) {
            $daysLimit = $room->secret_message_days_before;

            if ($daysLimit === null) {
                $hasActiveEvent = $reservation->events->contains(fn ($e) => $e->end->isFuture());
                if ($hasActiveEvent) {
                    $canView = true;
                } else {
                    $message = __('All events for this reservation have ended.');
                }
            } else {
                $now = now();
                $hasVisibleEvent = $reservation->events->contains(function ($event) use ($daysLimit, $now) {
                    return $event->start->copy()->subDays($daysLimit)->lte($now) && $event->end->gt($now);
                });

                if ($hasVisibleEvent) {
                    $canView = true;
                } else {
                    $futureEvents = $reservation->events->filter(fn ($e) => $e->end->isFuture());
                    if ($futureEvents->isEmpty()) {
                        $message = __('All events for this reservation have ended.');
                    } else {
                        $earliestStart = $futureEvents->min(fn ($e) => $e->start);
                        $availableFrom = $earliestStart->copy()->subDays($daysLimit);
                        $message = __('Access codes are not yet available.');
                    }
                }
            }
        }

        return view('reservations.codes', compact('reservation', 'room', 'canView', 'message', 'availableFrom'));
    }
}
