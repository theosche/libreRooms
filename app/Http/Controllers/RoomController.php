<?php

namespace App\Http\Controllers;

use App\Enums\OwnerUserRoles;
use App\Enums\ReservationStatus;
use App\Models\Owner;
use App\Models\Room;
use App\Models\SystemSettings;
use App\Validation\RoomRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Two views available:
     * - "mine": Rooms the user can manage (moderator+ on owner) - for reservation management
     * - "available": All rooms the user can access (public + private with access)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $view = $request->input('view', 'available'); // 'available' or 'mine'

        // Check if user can access "mine" view (must have moderator+ role on at least one owner)
        $canViewMine = $user && $user->can('viewMine', Room::class);

        if ($view === 'mine' && ! $canViewMine) {
            $view = 'available';
        }

        if ($view === 'mine') {
            // "mine" view: rooms from owners where user has moderator+ role
            $manageableOwnerIds = $user->getOwnerIdsWithMinRole(OwnerUserRoles::MODERATOR);

            $query = Room::with(['owner.contact', 'discounts', 'options'])
                ->whereIn('owner_id', $manageableOwnerIds);

            // Owners for filter: only those with moderator+ role
            $owners = Owner::with('contact')->whereIn('id', $manageableOwnerIds)->get();
        } else {
            // "available" view: all rooms accessible to the user (public + private with access)
            $query = Room::with(['owner.contact', 'discounts', 'options'])
                ->where('active', true)
                ->where(function ($q) use ($user) {
                    // Public rooms are always visible
                    $q->where('is_public', true);

                    // Add private rooms the user can access
                    if ($user) {
                        $accessibleOwnerIds = $user->getOwnerIdsWithAnyRole();
                        $directRoomIds = $user->rooms()->pluck('rooms.id');

                        $q->orWhere(function ($q2) use ($accessibleOwnerIds, $directRoomIds) {
                            $q2->where('is_public', false)
                                ->where(function ($q3) use ($accessibleOwnerIds, $directRoomIds) {
                                    $q3->whereIn('owner_id', $accessibleOwnerIds)
                                        ->orWhereIn('id', $directRoomIds);
                                });
                        });
                    }
                });

            // Owners for filter: those with public active rooms + those with private rooms user can access
            $ownersQuery = Owner::with('contact')
                ->whereHas('rooms', function ($q) {
                    $q->where('active', true)->where('is_public', true);
                });

            if ($user) {
                $accessibleOwnerIds = $user->getOwnerIdsWithAnyRole();
                $directRoomOwnerIds = Room::whereIn('id', $user->rooms()->pluck('rooms.id'))
                    ->pluck('owner_id');

                $privateAccessOwnerIds = $accessibleOwnerIds->merge($directRoomOwnerIds)->unique();

                $ownersQuery->orWhereIn('id', $privateAccessOwnerIds);
            }

            $owners = $ownersQuery->get();
        }

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->input('owner_id'));
        }

        $query->orderBy('name', 'asc');
        $rooms = $query->paginate(15)->appends($request->except('page'));

        return view('rooms.index', [
            'rooms' => $rooms,
            'owners' => $owners,
            'user' => $user,
            'view' => $view,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Room::class);

        $user = auth()->user();

        // Get owners where user has admin rights
        if ($user->is_global_admin) {
            $owners = Owner::with('contact')->get();
        } else {
            $ownerIds = $user->owners(  )->wherePivot('role', OwnerUserRoles::ADMIN->value)->pluck('owners.id');
            $owners = Owner::with('contact')->whereIn('id', $ownerIds)->get();
        }

        // Get owner timezones for JavaScript
        $ownerTimezones = $owners->mapWithKeys(function ($owner) {
            return [$owner->id => $owner->getTimezone()];
        });

        // Get owner caldav validity for JavaScript
        $ownersCaldavValid = $owners->mapWithKeys(function ($owner) {
            return [$owner->id => $owner->use_caldav && $owner->caldavSettings()->valid()];
        });

        return view('rooms.form', [
            'room' => null,
            'owners' => $owners,
            'ownerTimezones' => $ownerTimezones,
            'ownersCaldavValid' => $ownersCaldavValid,
            'systemSettings' => app(SystemSettings::class),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        // Validate
        RoomRules::prepare($request);
        $validated = $request->validate(RoomRules::rules($request));

        // Check if user has admin rights for the selected owner
        $owner = Owner::findOrFail($validated['owner_id']);
        $this->authorize('manageRooms', $owner);

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Room::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Create room
        $room = Room::create($validated);

        return redirect()->route('rooms.index', ['view' => 'mine'])
            ->with('success', 'La salle a été créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $room->load(['owner', 'discounts', 'options', 'customFields']);

        return response()->json($room);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        $user = auth()->user();

        // Get owners where user has admin rights
        if ($user->is_global_admin) {
            $owners = Owner::with('contact')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', OwnerUserRoles::ADMIN->value)->pluck('owners.id');
            $owners = Owner::with('contact')->whereIn('id', $ownerIds)->get();
        }

        // Get owner timezones for JavaScript
        $ownerTimezones = $owners->mapWithKeys(function ($owner) {
            return [$owner->id => $owner->getTimezone()];
        });

        // Get owner caldav validity for JavaScript
        $ownersCaldavValid = $owners->mapWithKeys(function ($owner) {
            return [$owner->id => $owner->use_caldav && $owner->caldavSettings()->valid()];
        });

        return view('rooms.form', [
            'room' => $room,
            'systemSettings' => app(SystemSettings::class),
            'owners' => $owners,
            'ownerTimezones' => $ownerTimezones,
            'ownersCaldavValid' => $ownersCaldavValid,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        // Validate
        RoomRules::prepare($request);
        $validated = $request->validate(RoomRules::rules($request));

        // Check if user has admin rights for the selected owner (in case it changed)
        $owner = Owner::findOrFail($validated['owner_id']);
        $this->authorize('manageRooms', $owner);

        // Generate slug from name if name changed
        if ($validated['name'] !== $room->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Room::where('slug', $validated['slug'])->where('id', '!=', $room->id)->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        // Update room
        $room->update($validated);

        return redirect()->route('rooms.index', ['view' => 'mine'])
            ->with('success', 'La salle a été mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        // Check for active reservations
        $activeReservations = $room->reservations()
            ->whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])
            ->exists();

        if ($activeReservations) {
            return redirect()->route('rooms.edit', $room)
                ->with('error', 'Cette salle a des réservations en cours (en attente ou confirmées). Veuillez les annuler avant de supprimer la salle.');
        }

        // Check for unpaid/uncancelled invoices
        $unpaidInvoices = $room->reservations()
            ->whereHas('invoice', function ($query) {
                $query->whereNull('paid_at')
                    ->whereNull('cancelled_at');
            })
            ->exists();

        if ($unpaidInvoices) {
            return redirect()->route('rooms.edit', $room)
                ->with('error', 'Cette salle a des factures impayées. Veuillez les marquer comme payées ou les annuler avant de supprimer la salle.');
        }

        $room->delete();

        return redirect()->route('rooms.index', ['view' => 'mine'])
            ->with('success', 'La salle a été supprimée avec succès.');
    }

    /**
     * Display calendar for a specific room.
     */
    public function calendar(Room $room): View
    {
        $this->authorize('view', $room);

        $user = auth()->user();
        $isAdmin = $user && $user->isAdminOf($room->owner);

        return view('rooms.calendar', [
            'room' => $room,
            'isAdmin' => $isAdmin,
        ]);
    }
}
