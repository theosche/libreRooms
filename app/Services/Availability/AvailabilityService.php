<?php

namespace App\Services\Availability;

use App\Enums\ReservationStatus;
use App\Models\ReservationEvent;
use App\Models\Room;
use App\Services\Caldav\CaldavClient;
use Carbon\Carbon;
use DateTimeZone;
use om\IcalParser;

class AvailabilityService
{
    private ?Room $room = null;

    private array $busySlots = [];

    public function __construct(
    ) {}

    public function loadBusySlotsCaldav(Room $room, string $timezone, ?Carbon $from, ?Carbon $to): array
    {
        $caldav = new CaldavClient;
        $caldav->connect($room);
        $ICSevents = $caldav->getEvents($from, $to);
        $fullIcs = '';
        foreach ($ICSevents as $ev) {
            $fullIcs .= $ev->getData().PHP_EOL;
        }
        if (empty($fullIcs)) {
            return [];
        }

        // Save current timezone - IcalParser has a bug that changes the global timezone
        $originalTimezone = date_default_timezone_get();

        $parser = new IcalParser;
        $parser->parseString($fullIcs);
        $busySlots = array_map(function ($e) use ($timezone) {
            return [
                'start' => Carbon::instance($e['DTSTART'])->setTimezone(new DateTimeZone($timezone)),
                'end' => Carbon::instance($e['DTEND'])->setTimezone(new DateTimeZone($timezone)),
                'uid' => $e['UID'],
                'title' => $e['SUMMARY'] ?? null,
                'description' => $e['DESCRIPTION'] ?? null,
            ];
        },
            (array) ($parser->getEvents())
        );

        // Restore original timezone
        date_default_timezone_set($originalTimezone);

        return $busySlots;
    }

    public function loadBusySlotsLocal(Room $room, string $timezone, ?Carbon $from, ?Carbon $to): array
    {
        // Always load all relations to return complete information
        $query = ReservationEvent::with(['reservation.tenant'])
            ->whereHas('reservation', function ($q) use ($room) {
                $q->where('room_id', $room->id)
                    ->where('status', ReservationStatus::CONFIRMED);
            });

        // Apply date filters if provided
        if ($from) {
            $query->where('end', '>', $from);
        }
        if ($to) {
            $query->where('start', '<', $to);
        }

        $events = $query->get(['id', 'reservation_id', 'start', 'end', 'uid']);

        return $events->map(function (ReservationEvent $event) use ($timezone) {
            return [
                'uid' => $event->uid,
                'start' => $event->start->copy()->setTimezone($timezone),
                'end' => $event->end->copy()->setTimezone($timezone),
                'title' => $event->reservation->title,
                'description' => $event->reservation->description,
                'tenant' => $event->reservation->tenant->display_name(),
            ];
        })->toArray();
    }

    public function loadBusySlots(Room $room, string $timezone = 'UTC', ?Carbon $from = null, ?Carbon $to = null): array
    {
        // send events starting from 3 months ago
        $from = $from ?? now()->subMonths(3)->utc();
        $this->room = $room;

        if ($room->usesCaldav()) {
            $this->busySlots = $this->loadBusySlotsCaldav($room, $timezone, $from, $to);

            return $this->busySlots;
        } else {
            $this->busySlots = $this->loadBusySlotsLocal($room, $timezone, $from, $to);

            return $this->busySlots;
        }
    }

    public function checkAvailability(Room $room, Carbon $start, Carbon $end, ?string $uid): bool
    {
        if ($this->room != $room) {
            $this->loadBusySlots($room);
        }

        foreach ($this->busySlots as $slot) {
            $hasOverlap = $start < $slot['end'] && $end > $slot['start'];
            $isDifferentEvent = $uid ? $slot['uid'] !== $uid : true;

            if ($hasOverlap && $isDifferentEvent) {
                return false;
            }
        }

        return true;
    }
}
