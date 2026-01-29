<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Services\Availability\AvailabilityService;
use App\Enums\CalendarViewModes;

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
                    $event['title'] = $slot['title'] ?? 'Sans titre';
                    $descriptionParts = [
                        '<strong>' . ($slot['title'] ?? 'Sans titre') . '</strong>',
                        'Début: ' . $slot['start']->format('d.m.Y H:i'),
                        'Fin: ' . $slot['end']->format('d.m.Y H:i'),
                    ];

                    if (!empty($slot['description'])) {
                        $descriptionParts[] = 'Description: ' . $slot['description'];
                    }

                    if (!empty($slot['tenant'])) {
                        $descriptionParts[] = 'Contact: ' . $slot['tenant'];
                    }

                    $event['extendedProps'] = [
                        'description' => implode("\n", $descriptionParts),
                    ];
                    break;

                case 'title':
                    $event['title'] = $slot['title'] ?? 'Sans titre';
                    $event['extendedProps'] = [
                        'description' => implode("\n", [
                            '<strong>' . ($slot['title'] ?? 'Sans titre') . '</strong>',
                            'Début: ' . $slot['start']->format('d.m.Y H:i'),
                            'Fin: ' . $slot['end']->format('d.m.Y H:i'),
                        ]),
                    ];
                    break;

                case 'slot':
                default:
                    $event['title'] = 'Occupé';
                    $event['extendedProps'] = [
                        'description' => implode("\n", [
                            '<strong>Occupé</strong>',
                            'Début: ' . $slot['start']->format('d.m.Y H:i'),
                            'Fin: ' . $slot['end']->format('d.m.Y H:i'),
                        ]),
                    ];
                    break;
            }

            return $event;
        }, $busySlots);

        return response()->json($events);
    }
}
