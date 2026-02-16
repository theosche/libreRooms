<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Room;
use App\Services\Mailer\MailService;
use App\Services\Reservation\PDFService;
use App\Services\Webdav\WebdavUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class InvoiceController extends Controller
{
    use RedirectsBack;

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
        $canViewAdmin = $user->can('viewAdmin', Room::class);

        if ($view === 'admin' && ! $canViewAdmin) {
            $view = 'mine';
        }

        if ($view === 'admin') {
            // Get all room IDs where user has moderator or admin rights (via owner role or direct room role)
            $roomIds = $user->getAccessibleRoomIds(UserRole::MODERATOR);

            // Build query - invoices for reservations on accessible rooms
            $query = Invoice::with([
                'reservation.room',
                'reservation.tenant',
                'owner',
            ])
                ->whereHas('reservation', function ($q) use ($roomIds) {
                    $q->whereIn('room_id', $roomIds);
                })
                ->orderBy('created_at', 'desc');

            // Get contacts for filter dropdown
            $contacts = Contact::whereIn('id', function ($q) use ($roomIds) {
                $q->select('tenant_id')
                    ->from('reservations')
                    ->whereIn('room_id', $roomIds)
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
        if (! $user->can('manageReservations', $invoice->reservation->room)) {
            return $this->redirectBack('invoices.index')->with('error', __('You do not have permission to send a reminder.'));
        }

        // Check status - must be LATE or TOO_LATE
        if (! in_array($invoice->computed_status, [InvoiceStatus::LATE, InvoiceStatus::TOO_LATE])) {
            return $this->redirectBack('invoices.index')->with('error', __('Cannot send reminder for this invoice (status: :status).', ['status' => $invoice->computed_status->label()]));
        }

        $owner = $invoice->owner;

        // Increment reminder count and update due_at
        $invoice->update([
            'reminder_count' => $invoice->reminder_count + 1,
            'issued_at' => now(),
            'due_at' => now()->addDays($owner->invoice_due_days_after_reminder),
        ]);

        // Send reminder email
        if (! $invoice->reservation->room->disable_mailer) {
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

        return $this->redirectBack('invoices.index')->with('success', __('Reminder #:count sent.', ['count' => $invoice->reminder_count]));
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->can('manageReservations', $invoice->reservation->room)) {
            return $this->redirectBack('invoices.index')->with('error', __('You do not have permission to edit this invoice.'));
        }

        // Check status - cannot mark paid or cancelled invoices
        if ($invoice->isFinal()) {
            return $this->redirectBack('invoices.index')->with('error', __('This invoice cannot be marked as paid.'));
        }

        $invoice->update([
            'paid_at' => now(),
        ]);

        return $this->redirectBack('invoices.index')->with('success', __('Invoice marked as paid.'));
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->can('manageReservations', $invoice->reservation->room)) {
            return $this->redirectBack('invoices.index')->with('error', __('You do not have permission to cancel this invoice.'));
        }

        // Cannot cancel paid invoices
        if ($invoice->paid_at !== null) {
            return $this->redirectBack('invoices.index')->with('error', __('Cannot cancel a paid invoice.'));
        }

        $invoice->update([
            'cancelled_at' => now(),
        ]);

        // Send cancellation email if requested
        if ($request->boolean('send_email') && ! $invoice->reservation->room->disable_mailer) {
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

        return $this->redirectBack('invoices.index')->with('success', __('Invoice cancelled.'));
    }

    /**
     * Recreate a cancelled invoice.
     */
    public function recreate(Request $request, Invoice $invoice): RedirectResponse
    {
        // Check permission
        $user = auth()->user();
        if (! $user->can('manageReservations', $invoice->reservation->room)) {
            return $this->redirectBack('invoices.index')->with('error', __('You do not have permission to recreate this invoice.'));
        }

        // Can only recreate cancelled invoices
        if (! $invoice->canRecreate()) {
            return $this->redirectBack('invoices.index')->with('error', __('Only a cancelled invoice linked to a confirmed reservation can be recreated.'));
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
        if ($request->boolean('send_email') && ! $invoice->reservation->room->disable_mailer) {
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

        return $this->redirectBack('invoices.index')->with('success', __('Invoice recreated with new due date: :date.', ['date' => $firstDueAt->format('d/m/Y')]));
    }
}
