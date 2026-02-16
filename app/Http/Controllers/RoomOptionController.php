<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
        $this->authorize('viewAnyOptions', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        // Build query
        $query = RoomOption::with(['room.owner.contact'])
            ->whereIn('room_id', $roomIds);

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy('room_id', 'asc')->orderBy('name', 'asc');
        $options = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
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
        $this->authorize('viewAnyOptions', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

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
        // Validate
        $validated = $request->validate(RoomOptionRules::rules());

        // Security: authorize on the target room
        $room = Room::findOrFail($validated['room_id']);
        $this->authorize('manageOptions', $room);

        RoomOption::create($validated);

        return redirect()->route('room-options.index')
            ->with('success', __('Option created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomOption $roomOption): View
    {
        $this->authorize('manageOptions', $roomOption->room);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

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
        $this->authorize('manageOptions', $roomOption->room);

        // Validate
        $validated = $request->validate(RoomOptionRules::rules($roomOption->id));

        // If room changed, check authorization on the new room too
        if ($validated['room_id'] != $roomOption->room_id) {
            $newRoom = Room::findOrFail($validated['room_id']);
            $this->authorize('manageOptions', $newRoom);
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
        $this->authorize('manageOptions', $roomOption->room);

        $roomOption->delete();

        return redirect()->route('room-options.index')
            ->with('success', __('Option deleted successfully.'));
    }
}
