<?php

namespace App\Http\Controllers;

use App\Enums\OwnerUserRoles;
use App\Enums\ReservationStatus;
use App\Models\Contact;
use App\Models\Owner;
use App\Models\Reservation;
use App\Models\SystemSettings;
use App\Models\User;
use App\Services\Settings\SettingsService;
use App\Validation\OwnerRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Owner::class);

        $user = auth()->user();

        // Get owners where user has moderator+ role
        if ($user->is_global_admin) {
            $query = Owner::with(['contact', 'users', 'rooms']);
        } else {
            // Get owners where user has at least moderator role
            $ownerIds = $user->getOwnerIdsWithMinRole(OwnerUserRoles::MODERATOR);
            $query = Owner::with(['contact', 'users', 'rooms'])
                ->whereIn('id', $ownerIds);
        }

        $owners = $query->orderBy('id', 'asc')->paginate(15);

        return view('owners.index', [
            'owners' => $owners,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Owner::class);

        $user = auth()->user();
        $systemSettings = app(SystemSettings::class);
        $settingsService = app(SettingsService::class);
        $contacts = $user->is_global_admin ? Contact::all() : $user->contacts;

        return view('owners.form', [
            'owner' => null,
            'systemSettings' => $systemSettings,
            'contacts' => $contacts,
            'hasDefaultMail' => $settingsService->hasDefaultMailSettings(),
            'hasDefaultCaldav' => $settingsService->hasDefaultCaldavSettings(),
            'hasDefaultWebdav' => $settingsService->hasDefaultWebdavSettings(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Owner::class);

        $user = auth()->user();

        // Validate
        OwnerRules::prepare($request);
        $validated = $request->validate(OwnerRules::rules());

        // Create owner
        $owner = Owner::create($validated);

        // Attach current user as admin
        $user->owners()->attach($owner->id, ['role' => OwnerUserRoles::ADMIN->value]);

        return redirect()->route('owners.index')
            ->with('success', 'Le propriétaire a été créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Owner $owner): View
    {
        $this->authorize('update', $owner);

        $user = auth()->user();
        $systemSettings = app(SystemSettings::class);
        $settingsService = app(SettingsService::class);
        $contacts = $user->is_global_admin ? Contact::all() : $user->contacts;

        // Ensure the current owner's contact is always in the list
        $currentContactId = $owner->contact_id;
        $currentContactInList = $contacts->contains('id', $currentContactId);

        if (! $currentContactInList && $currentContactId) {
            $currentContact = Contact::find($currentContactId);
            if ($currentContact) {
                $contacts = $contacts->push($currentContact);
            }
        }

        return view('owners.form', [
            'owner' => $owner,
            'systemSettings' => $systemSettings,
            'contacts' => $contacts,
            'currentContactId' => $currentContactId,
            'currentContactInUserList' => $currentContactInList,
            'hasDefaultMail' => $settingsService->hasDefaultMailSettings(),
            'hasDefaultCaldav' => $settingsService->hasDefaultCaldavSettings(),
            'hasDefaultWebdav' => $settingsService->hasDefaultWebdavSettings(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Owner $owner): RedirectResponse
    {
        $this->authorize('update', $owner);

        // Validate
        OwnerRules::prepare($request);
        $validated = $request->validate(OwnerRules::rules($owner));

        // Update owner
        $owner->update($validated);

        return redirect()->route('owners.index')
            ->with('success', 'Le propriétaire a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Owner $owner): RedirectResponse
    {
        $this->authorize('delete', $owner);

        $user = auth()->user();

        // Check if other users have access to this owner
        $otherUsers = $owner->users()->where('users.id', '!=', $user->id)->exists();

        if ($otherUsers && ! $user->is_global_admin) {
            // Detach only this user from the owner
            $user->owners()->detach($owner->id);

            return redirect()->route('owners.index')
                ->with('success', 'Le propriétaire a été retiré de votre liste.');
        }

        // Before deleting, check for active reservations across all rooms
        $roomIds = $owner->rooms()->pluck('id');
        $activeReservations = Reservation::whereIn('room_id', $roomIds)
            ->whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])
            ->exists();

        if ($activeReservations) {
            return redirect()->route('owners.edit', $owner)
                ->with('error', 'Ce propriétaire a des réservations en cours (en attente ou confirmées). Veuillez les annuler avant de supprimer le propriétaire.');
        }

        // Check for unpaid/uncancelled invoices
        $unpaidInvoices = Reservation::whereIn('room_id', $roomIds)
            ->whereHas('invoice', function ($query) {
                $query->whereNull('paid_at')
                    ->whereNull('cancelled_at');
            })
            ->exists();

        if ($unpaidInvoices) {
            return redirect()->route('owners.edit', $owner)
                ->with('error', 'Ce propriétaire a des factures impayées. Veuillez les marquer comme payées ou les annuler avant de supprimer le propriétaire.');
        }

        // Delete the owner entirely (this will cascade to rooms)
        $owner->delete();

        return redirect()->route('owners.index')
            ->with('success', 'Le propriétaire a été supprimé définitivement.');
    }

}
