<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Room;
use App\Models\RoomUnavailability;
use App\Support\DateHelper;
use App\Validation\RoomUnavailabilityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (! $user?->canAdminAnyOwner()) {
            abort(403, __('You must be an administrator of at least one owner to access this page.'));
        }

        // Get owner IDs where user has admin rights
        if ($user->is_global_admin) {
            $ownerIds = Owner::pluck('id');
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
        }

        // Build query
        $query = RoomUnavailability::with(['room.owner.contact'])
            ->whereHas('room', function ($q) use ($ownerIds) {
                $q->whereIn('owner_id', $ownerIds);
            });

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy('start', 'desc');
        $unavailabilities = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = Room::with('owner.contact')
            ->whereIn('owner_id', $ownerIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-unavailabilities.index', [
            'unavailabilities' => $unavailabilities,
            'rooms' => $rooms,
            'currentRoomId' => $currentRoomId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();

        // Check if user has admin rights for at least one owner
        if (! $user?->canAdminAnyOwner()) {
            abort(403, __('You must be an administrator of at least one owner to create an unavailability.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('room-unavailabilities.form', [
            'unavailability' => null,
            'rooms' => $rooms,
            'currentRoomId' => $request->input('room_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate (persmission check in validate)
        $validated = $request->validate(RoomUnavailabilityRules::rules());
        $timezone = Room::find($validated['room_id'])->getTimezone();
        $validated['start'] = DateHelper::fromLocalInput($validated['start'], $timezone);
        $validated['end'] = DateHelper::fromLocalInput($validated['end'], $timezone);
        // Create unavailability
        RoomUnavailability::create($validated);

        return redirect()->route('room-unavailabilities.index')
            ->with('success', __('Unavailability created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomUnavailability $roomUnavailability): View
    {
        $user = auth()->user();

        // Check if user has admin rights for this unavailability's room's owner
        if (! $user->isAdminOf($roomUnavailability->room->owner)) {
            abort(403, __('You do not have administration rights for this unavailability.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('room-unavailabilities.form', [
            'unavailability' => $roomUnavailability,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomUnavailability $roomUnavailability): RedirectResponse
    {
        $user = auth()->user();

        // Validate
        $validated = $request->validate(RoomUnavailabilityRules::rules());
        $timezone = $roomUnavailability->room->getTimezone();
        $validated['start'] = DateHelper::fromLocalInput($validated['start'], $timezone);
        $validated['end'] = DateHelper::fromLocalInput($validated['end'], $timezone);

        // Update unavailability
        $roomUnavailability->update($validated);

        return redirect()->route('room-unavailabilities.index')
            ->with('success', __('Unavailability updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomUnavailability $roomUnavailability): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has admin rights for this unavailability's room's owner
        if (! $user->isAdminOf($roomUnavailability->room->owner)) {
            return redirect()->route('room-unavailabilities.index')
                ->with('error', __('You do not have administration rights for this unavailability.'));
        }

        $roomUnavailability->delete();

        return redirect()->route('room-unavailabilities.index')
            ->with('success', __('Unavailability deleted successfully.'));
    }
}
