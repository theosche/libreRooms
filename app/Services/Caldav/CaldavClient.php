<?php

namespace App\Services\Caldav;

use App\Services\Ical\IcalService;
use App\Models\Room;
use App\Models\Owner;
use SimpleCalDAVClient;
use CalDAVClient as BaseCalDAVClient;
use DateTime;
use App\Models\ReservationEvent;

class CaldavClient
{
    protected SimpleCalDAVClient $client;

    public function __construct()
    {
        $this->client = new SimpleCalDAVClient();
    }

    public function connect(Room $room): void
    {
        // Do nothing if we don't use Caldav
        if (!$room->usesCaldav()) {
            return;
        }
        $caldavSettings = $room->owner->caldavSettings();
        $this->client->connect(
            rtrim($caldavSettings->url, '/') . '/' . $caldavSettings->user,
            $caldavSettings->user,
            $caldavSettings->pass
        );

        $calendars = $this->client->findCalendars();

        // Create the calendar if it doesn't exist
        if (!isset($calendars[$room->dav_calendar])) {
            $result = self::createCalendar(
                owner: $room->owner,
                calendarSlug: $room->dav_calendar,
                displayName: $room->name,
            );

            if (!$result['success']) {
                throw new \Exception("Impossible de créer le calendrier CalDAV : {$result['message']}");
            }

            // Refresh the calendars list
            $calendars = $this->client->findCalendars();
        }

        $this->client->setCalendar($calendars[$room->dav_calendar]);
    }

    /**
     * Create a new calendar on the CalDAV server.
     *
     * @param Owner $owner The owner with CalDAV settings
     * @param string $calendarSlug URL slug for the calendar (e.g., "my-calendar")
     * @param string $displayName Display name for the calendar
     * @param string|null $description Optional description
     * @param string|null $color Optional color in hex format (e.g., "#FF5733")
     * @return array{success: bool, message: string, calendar_id: ?string}
     */
    public static function createCalendar(
        Owner $owner,
        string $calendarSlug,
        string $displayName,
        ?string $description = null,
        ?string $color = null
    ): array {
        $caldavSettings = $owner->caldavSettings();

        if (!$caldavSettings || !$caldavSettings->url) {
            return [
                'success' => false,
                'message' => 'CalDAV non configuré pour ce propriétaire',
                'calendar_id' => null,
            ];
        }

        $baseUrl = rtrim($caldavSettings->url, '/') . '/' . $caldavSettings->user;

        // Create a CalDAVClient instance directly
        $client = new BaseCalDAVClient($baseUrl, $caldavSettings->user, $caldavSettings->pass);

        // Find the calendar-home-set
        $calendarHome = $client->FindCalendarHome();

        if (empty($calendarHome) || !isset($calendarHome[0])) {
            return [
                'success' => false,
                'message' => 'Impossible de trouver le calendar-home-set',
                'calendar_id' => null,
            ];
        }

        // Build the URL for the new calendar
        $calendarSlug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $calendarSlug);
        $newCalendarUrl = $client->first_url_part . rtrim($calendarHome[0], '/') . '/' . $calendarSlug . '/';

        // Build MKCALENDAR XML request (no XML declaration - causes issues with SabreDAV/Nextcloud)
        $xmlParts = ['<D:displayname>' . htmlspecialchars($displayName) . '</D:displayname>'];

        if ($description) {
            $xmlParts[] = '<C:calendar-description>' . htmlspecialchars($description) . '</C:calendar-description>';
        }

        if ($color) {
            // Apple calendar color format (with alpha)
            $colorWithAlpha = $color . 'FF';
            $xmlParts[] = '<A:calendar-color xmlns:A="http://apple.com/ns/ical/">' . $colorWithAlpha . '</A:calendar-color>';
        }

        $propsXml = implode('', $xmlParts);

        $xml = '<C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">'
            . '<D:set><D:prop>' . $propsXml . '</D:prop></D:set>'
            . '</C:mkcalendar>';

        // Send MKCALENDAR request
        $client->DoXMLRequest('MKCALENDAR', $xml, $newCalendarUrl);
        $httpCode = $client->GetHttpResultCode();

        if ($httpCode === '201') {
            return [
                'success' => true,
                'message' => 'Calendrier créé avec succès',
                'calendar_id' => $calendarSlug,
            ];
        }

        $errorMessages = [
            '403' => 'Permission refusée : vous n\'avez pas les droits pour créer un calendrier',
            '405' => 'Méthode non supportée : le serveur CalDAV ne permet pas la création de calendriers',
            '409' => 'Conflit : un calendrier avec ce nom existe peut-être déjà',
            '507' => 'Espace insuffisant sur le serveur',
        ];

        $message = $errorMessages[$httpCode] ?? "Erreur lors de la création du calendrier (HTTP {$httpCode})";

        return [
            'success' => false,
            'message' => $message,
            'calendar_id' => null,
        ];
    }

    public function getEvents(?DateTime $from, ?DateTime $to): array
    {
        return $this->client->getEvents(
            $from?->format('Ymd\THis\Z'),
            $to?->format('Ymd\THis\Z')
        );
    }

    public static function getHref(ReservationEvent $event): string
    {
        $caldavSettings = $event->reservation->room->owner->caldavSettings();

        $str = rtrim($caldavSettings->url,'/') . '/' . $caldavSettings->user . '/' . $event->reservation->room->dav_calendar.'/';
        return($str . $event->uid . '.ics');
    }

    public function createEvent(ReservationEvent $event): void
    {
        $ical = IcalService::getIcalData($event->eventDTO(addStatusInTitle: true));
        $this->client->create($ical);
    }

    public function updateEvent(ReservationEvent $event): void
    {
        $ical = IcalService::getIcalData($event->eventDTO(addStatusInTitle: true));
        $this->client->change(self::getHref($event), $ical, "*");
    }

    public function updateOrCreateEvent(ReservationEvent $event): void
    {
        try {
            $this->updateEvent($event);
        } catch (\Exception $e) {
            $this->createEvent($event);
        }
    }

    public function deleteEvent(ReservationEvent $event): void {
        $this->client->delete(self::getHref($event), "*");
    }
    public function deleteEventSilent(ReservationEvent $event): void {
        try {
            $this->deleteEvent($event);
        } catch (\Exception $e) {
            return;
        }
    }
}
