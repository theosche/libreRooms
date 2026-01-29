<?php

namespace App\Services\Ical;
use App\DTO\EventDTO;
use Illuminate\Support\Collection;

class IcalService
{
    public static function getIcalData(Collection|EventDTO $events): String {
        if (!$events instanceof Collection) {
            $events = new Collection([$events]);
        }
        $ICAL_FORMAT ='Ymd\THis\Z';
        $icalObject =
            "BEGIN:VCALENDAR
            CALSCALE:GREGORIAN
            VERSION:2.0
            PRODID:-//TEST AVEC ESPACES//FR
            ";
        foreach ($events as $event) {
            $icalObject .=
            "BEGIN:VEVENT
            CREATED:{$event->created->format($ICAL_FORMAT)}
            DTSTAMP:{$event->created->format($ICAL_FORMAT)}
            LAST-MODIFIED:{$event->updated->format($ICAL_FORMAT)}
            SEQUENCE:0
            UID:$event->uid
            DTSTART;TZID=UTC:{$event->start->format($ICAL_FORMAT)}
            DTEND;TZID=UTC:{$event->end->format($ICAL_FORMAT)}
            STATUS:$event->status
            SUMMARY:$event->title
            DESCRIPTION:
            LOCATION:$event->location
            END:VEVENT
            ";
        }
        $icalObject .= "END:VCALENDAR";

        // Remove leading spaces from each line while preserving spaces in content
        return preg_replace('/^\s+/m', '', $icalObject);
    }
}
