<?php

namespace App\Http\Controllers;

use App\Models\RoomOption;
use App\Models\Owner;
use App\Models\Room;
use App\Validation\RoomOptionRules;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Check if user has admin rights for at least one owner
        $canViewMine = $user && ($user->is_global_admin || $user->owners()->wherePivot('role', 'admin')->exists());

        if (!$canViewMine) {
            abort(403, 'Vous devez être administrateur d\'au moins un propriétaire pour accéder à cette page.');
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
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
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
            'user' => $user,
            'canViewMine' => $canViewMine,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();

        // Check if user has admin rights for at least one owner
        if (!$user->is_global_admin && !$user->owners()->wherePivot('role', 'admin')->exists()) {
            abort(403, 'Vous devez être administrateur d\'au moins un propriétaire pour créer une option.');
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
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Validate
        $validated = $request->validate(RoomOptionRules::rules());

        // Check if user has admin rights for the selected room's owner
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (!$user->is_global_admin && !$user->isAdminOf($room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour ce propriétaire.');
        }

        // Create option
        $option = RoomOption::create($validated);

        return redirect()->route('room-options.index')
            ->with('success', 'L\'option a été créée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomOption $roomOption): View
    {
        $user = auth()->user();

        // Check if user has admin rights for this option's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($roomOption->room->owner)) {
            abort(403, 'Vous n\'avez pas les droits d\'administration pour cette option.');
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

        // Check if user has admin rights for this option's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($roomOption->room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour cette option.');
        }

        // Validate
        $validated = $request->validate(RoomOptionRules::rules($roomOption->id));

        // Check if user has admin rights for the selected room's owner (in case it changed)
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (!$user->is_global_admin && !$user->isAdminOf($room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour le nouveau propriétaire.');
        }

        // Update option
        $roomOption->update($validated);

        return redirect()->route('room-options.index')
            ->with('success', 'L\'option a été mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomOption $roomOption): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has admin rights for this option's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($roomOption->room->owner)) {
            return redirect()->route('room-options.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour cette option.');
        }

        $roomOption->delete();

        return redirect()->route('room-options.index')
            ->with('success', 'L\'option a été supprimée avec succès.');
    }
}
