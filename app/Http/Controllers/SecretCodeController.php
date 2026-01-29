<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\View\View;

class SecretCodeController extends Controller
{
    /**
     * Display the secret codes for a confirmed reservation.
     * Public access via hash (obfuscated URL).
     */
    public function show(string $hash): View
    {
        $reservation = Reservation::where('hash', $hash)
            ->with(['room.owner', 'tenant', 'events'])
            ->firstOrFail();

        // Check if reservation is confirmed (only status that allows viewing codes)
        if ($reservation->status !== ReservationStatus::CONFIRMED) {
            abort(403, $this->getStatusMessage($reservation->status));
        }

        // Check if room has a secret message
        if (empty($reservation->room->secret_message)) {
            abort(404, 'Aucun code d\'accès disponible pour cette salle.');
        }

        return view('reservations.codes', [
            'reservation' => $reservation,
            'room' => $reservation->room,
        ]);
    }

    /**
     * Get a user-friendly message based on reservation status.
     */
    private function getStatusMessage(ReservationStatus $status): string
    {
        return match ($status) {
            ReservationStatus::PENDING => 'Les codes d\'accès ne sont disponibles qu\'une fois la réservation confirmée.',
            ReservationStatus::CANCELLED => 'Cette réservation a été annulée.',
            ReservationStatus::FINISHED => 'Cette réservation est terminée. Les codes d\'accès ne sont plus accessibles.',
        };
    }
}
