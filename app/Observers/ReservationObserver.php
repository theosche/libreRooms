<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\Caldav\CaldavClient;
use App\Services\Reservation\PDFService;
use App\Services\Webdav\WebdavUploader;

use function Illuminate\Support\defer;

class ReservationObserver
{
    /**
     * Handle the Reservation "deleting" event.
     * This runs BEFORE the reservation is deleted, allowing us to clean up related resources.
     */
    public function deleting(Reservation $reservation): void
    {
        $room = $reservation->room;

        // Load events if not already loaded
        $reservation->loadMissing(['events', 'invoice']);

        // Delete CalDAV events
        if ($room->usesCaldav()) {
            try {
                $caldav = app(CaldavClient::class);
                $caldav->connect($room);
                foreach ($reservation->events as $event) {
                    $caldav->deleteEventSilent($event);
                }
            } catch (\Exception $e) {
                // Log but don't block deletion
                \Log::warning('Failed to delete CalDAV events for reservation ' . $reservation->id . ': ' . $e->getMessage());
            }
        }

        // Delete WebDAV files (deferred to not block deletion)
        if ($room->usesWebdav()) {
            $prebookPath = PDFService::getPrebookFilename($reservation);
            $invoicePath = $reservation->invoice ? PDFService::getInvoiceFilename($reservation->invoice) : null;
            $owner = $room->owner;

            defer(function () use ($prebookPath, $invoicePath, $owner) {
                $uploader = new WebdavUploader();
                $uploader->setOwner($owner);

                // Delete prebook PDF
                $uploader->deleteSilent($prebookPath);

                // Delete invoice PDF if exists
                if ($invoicePath) {
                    $uploader->deleteSilent($invoicePath);
                }
            });
        }

        // Cancel invoice if exists and not paid (will be deleted by cascade anyway,
        // but this ensures it's marked as cancelled in case of foreign key issues)
        if ($reservation->invoice && $reservation->invoice->paid_at === null && $reservation->invoice->cancelled_at === null) {
            $reservation->invoice->update([
                'cancelled_at' => now(),
            ]);
        }
    }
}
