<?php

namespace App\Http\Controllers;

use App\Enums\CalendarViewModes;
use App\Models\Room;
use App\Services\Availability\AvailabilityService;

class AvailabilityController
{
    public function show(Room $room, AvailabilityService $service)
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->is_global_admin || $user->isAdminOf($room->owner));
        $timezone = $room->getTimezone();

        // Determine view mode: admin always sees FULL
        $viewMode = $isAdmin ? CalendarViewModes::FULL : $room->calendar_view_mode;

        // Load all busy slots (service returns all information)
        $busySlots = $service->loadBusySlots($room, $timezone);

        // Format events based on view mode
        $events = array_map(function ($slot) use ($viewMode) {
            $event = [
                'start' => $slot['start']->format('Y-m-d\TH:i'),
                'end' => $slot['end']->format('Y-m-d\TH:i'),
                'uid' => $slot['uid'],
            ];

            // Add title and description based on view mode
            switch ($viewMode->value) {
                case 'full':
                    $event['title'] = $slot['title'] ?? __('Untitled');
                    $descriptionParts = [
                        '<strong>'.($slot['title'] ?? __('Untitled')).'</strong>',
                        __('Start').': '.$slot['start']->format('d.m.Y H:i'),
                        __('End').': '.$slot['end']->format('d.m.Y H:i'),
                    ];

                    if (! empty($slot['description'])) {
                        $descriptionParts[] = __('Description').': '.$slot['description'];
                    }

                    if (! empty($slot['tenant'])) {
                        $descriptionParts[] = __('Contact').': '.$slot['tenant'];
                    }

                    $event['extendedProps'] = [
                        'description' => implode("\n", $descriptionParts),
                    ];
                    break;

                case 'title':
                    $event['title'] = $slot['title'] ?? __('Untitled');
                    $event['extendedProps'] = [
                        'description' => implode("\n", [
                            '<strong>'.($slot['title'] ?? __('Untitled')).'</strong>',
                            __('Start').': '.$slot['start']->format('d.m.Y H:i'),
                            __('End').': '.$slot['end']->format('d.m.Y H:i'),
                        ]),
                    ];
                    break;

                case 'slot':
                default:
                    $event['title'] = __('Occupied');
                    $event['extendedProps'] = [
                        'description' => implode("\n", [
                            '<strong>'.__('Occupied').'</strong>',
                            __('Start').': '.$slot['start']->format('d.m.Y H:i'),
                            __('End').': '.$slot['end']->format('d.m.Y H:i'),
                        ]),
                    ];
                    break;
            }

            return $event;
        }, $busySlots);

        $unavailabilities = $room->unavailabilities->map(function ($u) {
            $start = $u->startLocalTz();
            $end = $u->endLocalTz();
            $event = [
                'start' => $start->format('Y-m-d\TH:i'),
                'end' => $end->format('Y-m-d\TH:i'),
                'title' => $u->title ?? __('Unavailable'),
                'color' => '#fea2a2',
            ];
            $event['extendedProps'] = [
                'description' => implode("\n", [
                    '<strong>'.$event['title'].'</strong>',
                    __('Start').': '.$start->format('d.m.Y H:i'),
                    __('End').': '.$end->format('d.m.Y H:i'),
                ]),
            ];

            return $event;
        });

        return response()->json([
            'events' => $events,
            'unavailabilities' => $unavailabilities,
            'businessHours' => $this->buildBusinessHours($room),
        ]);
    }

    /**
     * Build the businessHours configuration for FullCalendar.
     */
    private function buildBusinessHours(Room $room): array|bool
    {
        if (! $room->allowed_weekdays && ! $room->day_start_time && ! $room->day_end_time) {
            return false; // No restrictions = no businessHours
        }

        return [
            // Convert ISO weekday (1-7, Mon-Sun) to FullCalendar format (0-6, Sun-Sat)
            'daysOfWeek' => $room->allowed_weekdays
                ? array_map(fn ($d) => $d % 7, $room->allowed_weekdays) // 1→1, 2→2, ..., 7→0
                : [0, 1, 2, 3, 4, 5, 6],
            'startTime' => $room->day_start_time ? substr($room->day_start_time, 0, 5) : '00:00',
            'endTime' => $room->day_end_time ? substr($room->day_end_time, 0, 5) : '24:00',
        ];
    }
}
