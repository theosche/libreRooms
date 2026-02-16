<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
        $this->authorize('viewAnyUnavailabilities', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::MODERATOR);

        // Build query
        $query = RoomUnavailability::with(['room.owner.contact'])
            ->whereIn('room_id', $roomIds);

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy('start', 'desc');
        $unavailabilities = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
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
        $this->authorize('viewAnyUnavailabilities', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::MODERATOR);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

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
        // Validate
        $validated = $request->validate(RoomUnavailabilityRules::rules());

        // Security: authorize on the target room
        $room = Room::findOrFail($validated['room_id']);
        $this->authorize('manageUnavailabilities', $room);

        $timezone = $room->getTimezone();
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
        $this->authorize('manageUnavailabilities', $roomUnavailability->room);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::MODERATOR);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

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
        // Security: authorize on the current room
        $this->authorize('manageUnavailabilities', $roomUnavailability->room);

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
        $this->authorize('manageUnavailabilities', $roomUnavailability->room);

        $roomUnavailability->delete();

        return redirect()->route('room-unavailabilities.index')
            ->with('success', __('Unavailability deleted successfully.'));
    }
}
