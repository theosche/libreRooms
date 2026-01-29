<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\OwnerUserRoles;
use App\Models\Contact;
use App\Models\Invoice;
use App\Services\Mailer\MailService;
use App\Services\Reservation\PDFService;
use App\Services\Webdav\WebdavUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class InvoiceController extends Controller
{
    public function __construct(
        private MailService $mail,
    ) {}
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $view = $request->input('view', 'mine'); // 'mine' or 'admin'

        // Check if user can access admin view (moderator or admin role)
        $canViewAdmin = $user->is_global_admin ||
            $user->owners()
                ->wherePivotIn('role', [OwnerUserRoles::ADMIN->value, OwnerUserRoles::MODERATOR->value])
                ->exists();

        if ($view === 'admin' && ! $canViewAdmin) {
            $view = 'mine';
        }

        if ($view === 'admin') {
            // Get all owner IDs where user has moderator or admin rights
            if ($user->is_global_admin) {
                $ownerIds = \App\Models\Owner::pluck('id');
            } else {
                $ownerIds = $user->owners()
                    ->wherePivotIn('role', [OwnerUserRoles::ADMIN->value, OwnerUserRoles::MODERATOR->value])
                    ->pluck('owners.id');
            }

            // Build query
            $query = Invoice::with([
                'reservation.room',
                'reservation.tenant',
                'owner',
            ])
                ->whereIn('owner_id', $ownerIds)
                ->orderBy('created_at', 'desc');

            // Get contacts for filter dropdown
            $contacts = Contact::whereIn('id', function ($q) use ($ownerIds) {
                $q->select('tenant_id')
                    ->from('reservations')
                    ->join('invoices', 'reservations.id', '=', 'invoices.reservation_id')
                    ->whereIn('invoices.owner_id', $ownerIds)
                    ->distinct();
            })->get();
        } else {
            // Get all contact IDs for the logged-in user
            $contactIds = $user->contacts()->pluck('contacts.id');

            // Build query - invoices where tenant is one of user's contacts
            $query = Invoice::with([
                'reservation.room',
                'reservation.tenant',
                'owner',
            ])
                ->whereHas('reservation', function ($q) use ($contactIds) {
                    $q->whereIn('tenant_id', $contactIds);
                })
                ->orderBy('created_at', 'desc');

            // User's contacts for filter dropdown
            $contacts = $user->contacts;
        }

        // Apply filters
        if ($request->filled('tenant_id')) {
            $query->whereHas('reservation', function ($q) use ($request) {
                $q->where('tenant_id', $request->input('tenant_id'));
            });
        }

        if ($request->filled('status')) {
            // Special handling for "late" filter (combines LATE and TOO_LATE)
            if ($request->input('status') === 'late') {
                $query->late();
            } else {
                $status = InvoiceStatus::tryFrom($request->input('status'));
                if ($status) {
                    $query->withComputedStatus($status);
                }
            }
        }

        $invoices = $query->paginate(15)->appends($request->except('page'));

        return view('invoices.index', [
            'invoices' => $invoices,
            'contacts' => $contacts,
            'user' => $user,
            'view' => $view,
            'canViewAdmin' => $canViewAdmin,
        ]);
    }

    /**
     * Send a payment reminder for an invoice.
     */
    public function remind(Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (!$user->canManageReservationsFor($invoice->reservation->room)) {
            return back()->with('error', 'Vous n\'avez pas la permission d\'envoyer un rappel.');
        }

        // Check status - must be LATE or TOO_LATE
        if (!in_array($invoice->computed_status, [InvoiceStatus::LATE, InvoiceStatus::TOO_LATE])) {
            return back()->with('error', 'Impossible d\'envoyer un rappel pour cette facture (statut: ' . $invoice->computed_status->label() . ').');
        }

        $owner = $invoice->owner;

        // Increment reminder count and update due_at
        $invoice->update([
            'reminder_count' => $invoice->reminder_count + 1,
            'issued_at' => now(),
            'due_at' => now()->addDays($owner->invoice_due_days_after_reminder),
        ]);

        // Send reminder email
        if (!$invoice->reservation->room->disable_mailer) {
            $this->mail->sendReminder($invoice);
        }

        // Upload reminder PDF to WebDAV
        if ($invoice->reservation->room->usesWebdav()) {
            defer(function () use ($invoice) {
                $path = PDFService::getReminderFilename($invoice);
                $pdfContents = (new PDFService)->generateReminderPDF($invoice);

                (new WebdavUploader)
                    ->setOwner($invoice->owner)
                    ->uploadFileContents($pdfContents, $path);
            });
        }

        return back()->with('success', 'Rappel n°'.$invoice->reminder_count.' envoyé.');
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->canManageReservationsFor($invoice->reservation->room)) {
            return back()->with('error', 'Vous n\'avez pas la permission de modifier cette facture.');
        }

        // Check status - cannot mark paid or cancelled invoices
        if ($invoice->isFinal()) {
            return back()->with('error', 'Cette facture ne peut pas être marquée comme payée.');
        }

        $invoice->update([
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Facture marquée comme payée.');
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->canManageReservationsFor($invoice->reservation->room)) {
            return back()->with('error', 'Vous n\'avez pas la permission d\'annuler cette facture.');
        }

        // Cannot cancel paid invoices
        if ($invoice->paid_at !== null) {
            return back()->with('error', 'Impossible d\'annuler une facture payée.');
        }

        $invoice->update([
            'cancelled_at' => now(),
        ]);

        // Send cancellation email if requested
        if ($request->boolean('send_email') && !$invoice->reservation->room->disable_mailer) {
            $reason = $request->input('reason', '');
            $this->mail->cancelInvoice($invoice->reservation, $reason);
        }

        // Delete PDF from WebDAV (silently)
        if ($invoice->reservation->room->usesWebdav()) {
            defer(function () use ($invoice) {
                $path = PDFService::getInvoiceFilename($invoice);
                (new WebdavUploader)
                    ->setOwner($invoice->owner)
                    ->deleteSilent($path);
            });
        }

        return back()->with('success', 'Facture annulée.');
    }

    /**
     * Recreate a cancelled invoice.
     */
    public function recreate(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->canManageReservationsFor($invoice->reservation->room)) {
            return back()->with('error', 'Vous n\'avez pas la permission de recréer cette facture.');
        }

        // Can only recreate cancelled invoices
        if (! $invoice->canRecreate()) {
            return back()->with('error', 'Seule une facture annulée liée à une réservation confirmée peut être recréée.');
        }

        $firstDueAt = Invoice::calculateFirstDueAt($invoice->reservation, isRecreate: true);

        $invoice->update([
            'amount' => $invoice->reservation->finalPrice(),
            'first_issued_at' => now(),
            'issued_at' => now(),
            'first_due_at' => $firstDueAt,
            'due_at' => $firstDueAt,
            'cancelled_at' => null,
            'paid_at' => null,
            'reminder_count' => 0,
        ]);

        // Send invoice email if requested
        if ($request->boolean('send_email') && !$invoice->reservation->room->disable_mailer) {
            $complement = $request->input('reason', '');
            $this->mail->sendInvoice($invoice->reservation, $complement);
        }

        // Upload PDF to WebDAV
        if ($invoice->reservation->room->usesWebdav()) {
            defer(function () use ($invoice) {
                $reservation = $invoice->reservation;
                $path = PDFService::getInvoiceFilename($invoice);
                $pdfContents = (new PDFService)->generateInvoicePDF($reservation);

                (new WebdavUploader)
                    ->setOwner($invoice->owner)
                    ->uploadFileContents($pdfContents, $path);
            });
        }

        return back()->with('success', 'Facture recréée avec nouvelle échéance au '.$firstDueAt->format('d/m/Y').'.');
    }
}
