<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\Contact;
use App\Models\User;
use App\Validation\ContactRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    use RedirectsBack;

    /**
     * Display a listing of the resource.
     *
     * Two views available for global admins:
     * - "mine": Contacts belonging to the user (default)
     * - "all": All contacts in the system (global admin only)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $view = $request->input('view', 'mine');

        // Only global admins can view all contacts
        if ($view === 'all' && ! $user->is_global_admin) {
            $view = 'mine';
        }

        if ($view === 'all') {
            // Global admin: show all contacts
            $contacts = Contact::with('users')
                ->orderBy('entity_name', 'asc')
                ->orderBy('last_name', 'asc')
                ->paginate(15)
                ->appends($request->except('page'));
        } else {
            // Regular view: user's contacts only
            $contacts = $user->contacts()
                ->with('users')
                ->orderBy('entity_name', 'asc')
                ->orderBy('last_name', 'asc')
                ->paginate(15)
                ->appends($request->except('page'));
        }

        return view('contacts.index', [
            'contacts' => $contacts,
            'user' => $user,
            'view' => $view,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('contacts.form', ['contact' => null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Prepare data (nullify fields based on conditions)
        ContactRules::prepare($request);

        // Validate
        $validated = $request->validate(ContactRules::rules($request));

        // Remove has_invoice_email from validated data (it's not a field in the contacts table)
        unset($validated['has_invoice_email']);
        unset($validated['contact_id']);
        $validated['type'] = $validated['contact_type'];
        unset($validated['contact_type']);

        // Create contact
        $contact = Contact::create($validated);

        // Attach to current user
        $user->contacts()->attach($contact->id);

        return $this->redirectBack('contacts.index')
            ->with('success', __('Contact created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $user = auth()->user();

        // Global admins can edit any contact, otherwise check ownership
        if (! $user->canAccessContact($contact)) {
            abort(403, __('You do not have access to this contact.'));
        }

        return view('contacts.form', [
            'contact' => $contact,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $user = auth()->user();

        // Global admins can update any contact, otherwise check ownership
        if (! $user->canAccessContact($contact)) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('You do not have access to this contact.'));
        }

        // Prepare data
        ContactRules::prepare($request);

        // Validate
        $validated = $request->validate(ContactRules::rules($request));

        // Remove fields not in contacts table
        unset($validated['has_invoice_email']);
        unset($validated['contact_id']);

        // Update contact
        $contact->update($validated);

        return $this->redirectBack('contacts.index')
            ->with('success', __('Contact updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $user = auth()->user();
        $userOwnsContact = $user->contacts()->where('contacts.id', $contact->id)->exists();

        // Global admins can delete any contact, otherwise check ownership
        if (! $user->is_global_admin && ! $userOwnsContact) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('You do not have access to this contact.'));
        }

        // If user owns the contact and other users have access, just detach
        if ($userOwnsContact) {
            $otherUsers = $contact->users()->where('users.id', '!=', $user->id)->exists();

            if ($otherUsers) {
                // Detach only this user from the contact
                $user->contacts()->detach($contact->id);

                return redirect()->back()
                    ->with('success', __('Contact removed from your list.'));
            }
        }

        if ($contact->owners()->exists()) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('This contact cannot be deleted because it is used for an owner.'));
        }

        // Before deleting, check for active reservations
        $activeReservations = $contact->reservations()
            ->whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])
            ->exists();

        if ($activeReservations) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('This contact has active reservations (pending or confirmed). Please cancel them before deleting the contact.'));
        }

        // Check for unpaid/uncancelled invoices
        $unpaidInvoices = $contact->reservations()
            ->whereHas('invoice', function ($query) {
                $query->whereNull('paid_at')
                    ->whereNull('cancelled_at');
            })
            ->exists();

        if ($unpaidInvoices) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('This contact has unpaid invoices. Please mark them as paid or cancel them before deleting the contact.'));
        }

        // Delete the contact entirely
        $contact->delete();

        return redirect()->back()
            ->with('success', __('Contact deleted permanently.'));
    }

    /**
     * Share a contact with another user.
     */
    public function share(Request $request, Contact $contact): RedirectResponse
    {
        $currentUser = auth()->user();

        // Global admins can share any contact, otherwise check ownership
        if (! $currentUser->canAccessContact($contact)) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('You do not have access to this contact.'));
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Find the user by email
        $userToShareWith = User::where('email', $validated['email'])->first();

        if (! $userToShareWith) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('No user found with this email.'));
        }

        if ($userToShareWith->id === $currentUser->id && ! $currentUser->is_global_admin) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('You cannot share a contact with yourself.'));
        }

        // Check if already shared
        if ($userToShareWith->contacts()->where('contacts.id', $contact->id)->exists()) {
            return $this->redirectBack('contacts.index')
                ->with('error', __('This contact is already shared with this user.'));
        }

        // Share the contact
        $userToShareWith->contacts()->attach($contact->id);

        return redirect()->back()
            ->with('success', __('Contact shared with :name.', ['name' => $userToShareWith->name]));
    }
}
