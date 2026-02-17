<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\Room;
use App\Models\RoomDiscount;
use App\Validation\RoomDiscountRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomDiscountController extends Controller
{
    use RedirectsBack;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAnyDiscounts', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        // Build query
        $query = RoomDiscount::with(['room.owner.contact'])
            ->whereIn('room_id', $roomIds);

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy(Room::select('name')->whereColumn('rooms.id', 'room_discounts.room_id'))->orderBy('name', 'asc');
        $discounts = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-discounts.index', [
            'discounts' => $discounts,
            'rooms' => $rooms,
            'currentRoomId' => $currentRoomId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('viewAnyDiscounts', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-discounts.form', [
            'discount' => null,
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
        $validated = $request->validate(RoomDiscountRules::rules());

        // Security: authorize on the target room
        $room = Room::findOrFail($validated['room_id']);
        $this->authorize('manageDiscounts', $room);

        // Create discount
        RoomDiscount::create($validated);

        return $this->redirectBack('room-discounts.index')
            ->with('success', __('Discount created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomDiscount $roomDiscount): View
    {
        $this->authorize('manageDiscounts', $roomDiscount->room);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-discounts.form', [
            'discount' => $roomDiscount,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomDiscount $roomDiscount): RedirectResponse
    {
        $this->authorize('manageDiscounts', $roomDiscount->room);

        // Validate
        $validated = $request->validate(RoomDiscountRules::rules($roomDiscount->id));

        // If room changed, check authorization on the new room too
        if ($validated['room_id'] != $roomDiscount->room_id) {
            $newRoom = Room::findOrFail($validated['room_id']);
            $this->authorize('manageDiscounts', $newRoom);
        }

        // Update discount
        $roomDiscount->update($validated);

        return $this->redirectBack('room-discounts.index')
            ->with('success', __('Discount updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomDiscount $roomDiscount): RedirectResponse
    {
        $this->authorize('manageDiscounts', $roomDiscount->room);

        $roomDiscount->delete();

        return $this->redirectBack('room-discounts.index')
            ->with('success', __('Discount deleted successfully.'));
    }
}
