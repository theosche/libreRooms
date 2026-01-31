<?php

namespace App\Services\Mailer;

use App\Models\Invoice;
use App\Models\Owner;
use App\Models\Reservation;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Redirect email to debug address if configured.
     * When MAIL_DEBUG_REDIRECT is set in .env, all emails go to that address.
     */
    private function redirectIfDebug(mixed $email): mixed
    {
        $debugRedirect = config('mail.debug_redirect');

        if ($debugRedirect) {
            return $debugRedirect;
        }

        return $email;
    }

    public function sendNewReservation(Reservation $reservation): void
    {
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;
        $this->configureMailer($owner);

        Mail::send('emails.new-reservation-admin', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
        ], function ($message) use ($room, $owner) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($owner->contact->email), $owner->contact->display_name())
                ->subject(ucfirst($room->name).' - Nouvelle demande de réservation à contrôler');
        });
        Mail::send('emails.new-reservation', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
        ], function ($message) use ($room, $owner, $tenant) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->email), $tenant->display_name())
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject(ucfirst($room->name).' - Votre demande de réservation');
        });
    }

    public function sendConfirmation(Reservation $reservation): void
    {
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;

        $this->configureMailer($owner);

        Mail::send('emails.confirmation', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
            'tenant' => $tenant,
            'invoice' => $reservation->invoice,
        ], function ($message) use ($room, $owner, $tenant) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->bothEmailsUnique()))
                ->cc($this->redirectIfDebug($owner->contact->email), $owner->contact->display_name())
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject(ucfirst($room->name).' - Confirmation de réservation et facture (mail automatique)');
        });
    }

    // Function used to send invoice only, when invoice is regenerated
    // Function used to cancel invoice only

    public function sendInvoice(Reservation $reservation, ?string $complement = null): void
    {
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;

        $this->configureMailer($owner);

        Mail::send('emails.new-invoice', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
            'invoice' => $reservation->invoice,
            'complement' => $complement,
        ], function ($message) use ($room, $owner, $tenant) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->invoiceEmail()), $tenant->display_name())
                ->cc($this->redirectIfDebug([$tenant->email, $owner->contact->email]))
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject(ucfirst($room->name).' - Nouvelle facture (mail automatique)');
        });
    }

    public function cancelInvoice(Reservation $reservation, ?string $complement = null): void
    {
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;

        $this->configureMailer($owner);

        Mail::send('emails.cancel-invoice', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
            'invoice' => $reservation->invoice,
            'complement' => $complement,
        ], function ($message) use ($reservation, $owner, $tenant) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->invoiceEmail()), $tenant->display_name())
                ->cc($this->redirectIfDebug([$tenant->email, $owner->contact->email]))
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject('Annulation de la facture '.$reservation->invoice->number.' (mail automatique)');
        });
    }

    public function sendCancellation(Reservation $reservation, ?string $complement = null): void
    {
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;

        $this->configureMailer($owner);

        Mail::send('emails.cancellation', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
            'tenant' => $tenant,
            'complement' => $complement,
        ], function ($message) use ($room, $owner, $tenant) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->bothEmailsUnique()))
                ->cc($this->redirectIfDebug($owner->contact->email), $owner->contact->display_name())
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject(ucfirst($room->name).' - Annulation de votre réservation (mail automatique)');
        });
    }

    public function sendReminder(Invoice $invoice): void
    {
        $reservation = $invoice->reservation;
        $room = $reservation->room;
        $owner = $room->owner;
        $tenant = $reservation->tenant;

        $this->configureMailer($owner);

        Mail::send('emails.reminder', [
            'reservation' => $reservation,
            'room' => $room,
            'owner' => $owner,
            'tenant' => $tenant,
            'invoice' => $invoice,
        ], function ($message) use ($room, $owner, $tenant, $invoice) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($tenant->invoiceEmail()), $tenant->display_name())
                ->cc($this->redirectIfDebug([$tenant->email, $owner->contact->email]))
                ->replyTo($owner->contact->email, $owner->contact->display_name())
                ->subject("Location de {$room->name} - Facture à payer - {$invoice->formatedReminderCount()}");
        });
    }

    public function sendLateInvoicesReminder(Owner $owner, int $lateCount): void
    {
        $this->configureMailer($owner);

        Mail::send('emails.late-invoices-reminder', [
            'owner' => $owner,
            'lateCount' => $lateCount,
        ], function ($message) use ($owner, $lateCount) {
            $message->from($owner->mailSettings()->user, $owner->contact->display_name())
                ->to($this->redirectIfDebug($owner->contact->email), $owner->contact->display_name())
                ->subject("Rappel : {$lateCount} facture".($lateCount > 1 ? 's' : '').' en retard');
        });
    }

    protected function configureMailer($owner): void
    {
        // Use centralized mailer configuration from SettingsService
        app(SettingsService::class)->configureMailer($owner);
    }
}
