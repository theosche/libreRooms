<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Services\Reservation\PDFService;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function __construct(
        private PDFService $pdfService,
    ) {}

    /**
     * Generate and return the prebook PDF for a reservation.
     * Public access via hash (obfuscated URL).
     */
    public function prebook(string $hash): Response
    {
        $reservation = Reservation::where('hash', $hash)
            ->where('status', '!=', ReservationStatus::CANCELLED)
            ->with(['room.owner.contact', 'tenant', 'events'])
            ->firstOrFail();

        $pdfContent = $this->pdfService->generatePrebookingPDF($reservation);
        $filename = 'prereservation-' . $reservation->room->slug . '-' . $reservation->id . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    /**
     * Generate and return the invoice PDF for a reservation.
     * Public access via hash (obfuscated URL).
     */
    public function invoice(string $hash): Response
    {
        $reservation = Reservation::where('hash', $hash)
            ->with(['room.owner.contact', 'tenant', 'events', 'invoice'])
            ->firstOrFail();

        if (! $reservation->invoice) {
            abort(404, __('No invoice for this reservation.'));
        }

        $pdfContent = $this->pdfService->generateInvoicePDF($reservation);

        $filename = 'facture-' . $reservation->invoice->number . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    /**
     * Generate and return the reminder PDF for an invoice.
     * Public access via hash (obfuscated URL).
     */
    public function reminder(string $hash): Response
    {
        $reservation = Reservation::where('hash', $hash)
            ->with(['room.owner.contact', 'tenant', 'events', 'invoice'])
            ->firstOrFail();

        if (! $reservation->invoice) {
            abort(404, __('No invoice for this reservation.'));
        }

        if ($reservation->invoice->reminder_count < 1) {
            abort(404, __('No reminder for this invoice.'));
        }

        $pdfContent = $this->pdfService->generateReminderPDF($reservation->invoice);

        $filename = 'rappel-' . $reservation->invoice->number . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
