<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Room;
use App\Models\RoomOption;
use App\Validation\RoomOptionRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomOptionController extends Controller
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

        // Get room IDs where user has admin rights
        if ($user->is_global_admin) {
            $ownerIds = Owner::pluck('id');
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
        }

        // Build query
        $query = RoomOption::with(['room.owner.contact'])
            ->whereHas('room', function ($q) use ($ownerIds) {
                $q->whereIn('owner_id', $ownerIds);
            });

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy('room_id', 'asc')->orderBy('name', 'asc');
        $options = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = \App\Models\Room::with('owner.contact')
            ->whereIn('owner_id', $ownerIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-options.index', [
            'options' => $options,
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
            abort(403, __('You must be an administrator of at least one owner to create an option.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('room-options.form', [
            'option' => null,
            'rooms' => $rooms,
            'currentRoomId' => $request->input('room_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate (incl. permission check)
        $validated = $request->validate(RoomOptionRules::rules());

        RoomOption::create($validated);

        return redirect()->route('room-options.index')
            ->with('success', __('Option created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomOption $roomOption): View
    {
        $user = auth()->user();

        // Check if user has admin rights for this option's room's owner
        if (! $user->isAdminOf($roomOption->room->owner)) {
            abort(403, __('You do not have administration rights for this option.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('room-options.form', [
            'option' => $roomOption,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomOption $roomOption): RedirectResponse
    {
        $user = auth()->user();

        // Validate
        $validated = $request->validate(RoomOptionRules::rules($roomOption->id));

        // Check if user has admin rights for the selected room's owner (in case it changed)
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (! $user->is_global_admin && ! $user->isAdminOf($room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', __('You do not have administration rights for the new owner.'));
        }

        // Update option
        $roomOption->update($validated);

        return redirect()->route('room-options.index')
            ->with('success', __('Option updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomOption $roomOption): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has admin rights for this option's room's owner
        if (! $user->isAdminOf($roomOption->room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', __('You do not have administration rights for this option.'));
        }

        $roomOption->delete();

        return redirect()->route('room-options.index')
            ->with('success', __('Option deleted successfully.'));
    }
}
