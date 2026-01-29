<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Contact;
use App\Models\User;
use App\Validation\ContactRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get user's contacts with users who have access to each contact
        $contacts = $user->contacts()
            ->with('users')
            ->orderBy('entity_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->paginate(15);

        return view('contacts.index', [
            'contacts' => $contacts,
            'user' => $user,
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

        return redirect()->route('contacts.index')
            ->with('success', 'Le contact a été créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $user = auth()->user();

        // Check if contact belongs to this user
        if (!$user->contacts()->where('contacts.id', $contact->id)->exists()) {
            abort(403, 'Vous n\'avez pas accès à ce contact.');
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

        // Check if contact belongs to this user
        if (!$user->contacts()->where('contacts.id', $contact->id)->exists()) {
            return redirect()->route('contacts.index')
                ->with('error', 'Vous n\'avez pas accès à ce contact.');
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

        return redirect()->route('contacts.index')
            ->with('success', 'Le contact a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $user = auth()->user();

        // Check if contact belongs to this user
        if (! $user->contacts()->where('contacts.id', $contact->id)->exists()) {
            return redirect()->route('contacts.index')
                ->with('error', 'Vous n\'avez pas accès à ce contact.');
        }

        // Check if other users have access to this contact
        $otherUsers = $contact->users()->where('users.id', '!=', $user->id)->exists();

        if ($otherUsers) {
            // Detach only this user from the contact
            $user->contacts()->detach($contact->id);

            return redirect()->route('contacts.index')
                ->with('success', 'Le contact a été retiré de votre liste.');
        }

        // Before deleting, check for active reservations
        $activeReservations = $contact->reservations()
            ->whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])
            ->exists();

        if ($activeReservations) {
            return redirect()->route('contacts.index')
                ->with('error', 'Ce contact a des réservations en cours (en attente ou confirmées). Veuillez les annuler avant de supprimer le contact.');
        }

        // Check for unpaid/uncancelled invoices
        $unpaidInvoices = $contact->reservations()
            ->whereHas('invoice', function ($query) {
                $query->whereNull('paid_at')
                    ->whereNull('cancelled_at');
            })
            ->exists();

        if ($unpaidInvoices) {
            return redirect()->route('contacts.index')
                ->with('error', 'Ce contact a des factures impayées. Veuillez les marquer comme payées ou les annuler avant de supprimer le contact.');
        }

        // Delete the contact entirely
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Le contact a été supprimé définitivement.');
    }

    /**
     * Share a contact with another user.
     */
    public function share(Request $request, Contact $contact): RedirectResponse
    {
        $currentUser = auth()->user();

        // Check if contact belongs to current user
        if (!$currentUser->contacts()->where('contacts.id', $contact->id)->exists()) {
            return redirect()->route('contacts.index')
                ->with('error', 'Vous n\'avez pas accès à ce contact.');
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Find the user by email
        $userToShareWith = User::where('email', $validated['email'])->first();

        if (!$userToShareWith) {
            return redirect()->route('contacts.index')
                ->with('error', 'Aucun utilisateur trouvé avec cet email.');
        }

        if ($userToShareWith->id === $currentUser->id) {
            return redirect()->route('contacts.index')
                ->with('error', 'Vous ne pouvez pas partager un contact avec vous-même.');
        }

        // Check if already shared
        if ($userToShareWith->contacts()->where('contacts.id', $contact->id)->exists()) {
            return redirect()->route('contacts.index')
                ->with('error', 'Ce contact est déjà partagé avec cet utilisateur.');
        }

        // Share the contact
        $userToShareWith->contacts()->attach($contact->id);

        return redirect()->route('contacts.index')
            ->with('success', "Le contact a été partagé avec {$userToShareWith->name}.");
    }
}
