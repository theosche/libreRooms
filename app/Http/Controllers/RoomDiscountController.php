<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Room;
use App\Models\RoomDiscount;
use App\Validation\RoomDiscountRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomDiscountController extends Controller
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
        $query = RoomDiscount::with(['room.owner.contact'])
            ->whereHas('room', function ($q) use ($ownerIds) {
                $q->whereIn('owner_id', $ownerIds);
            });

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $query->orderBy('room_id', 'asc')->orderBy('name', 'asc');
        $discounts = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = \App\Models\Room::with('owner.contact')
            ->whereIn('owner_id', $ownerIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('room-discounts.index', [
            'discounts' => $discounts,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        // Check if user has admin rights for at least one owner
        if (! $user?->canAdminAnyOwner()) {
            abort(403, __('You must be an administrator of at least one owner to create a discount.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('room-discounts.form', [
            'discount' => null,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate
        $validated = $request->validate(RoomDiscountRules::rules());

        // Create discount
        RoomDiscount::create($validated);

        return redirect()->route('room-discounts.index')
            ->with('success', __('Discount created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomDiscount $roomDiscount): View
    {
        $user = auth()->user();

        // Check if user has admin rights for this discount's room's owner
        if (! $user->isAdminOf($roomDiscount->room->owner)) {
            abort(403, __('You do not have administration rights for this discount.'));
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

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
        $user = auth()->user();

        // Validate
        $validated = $request->validate(RoomDiscountRules::rules($roomDiscount->id));

        // Check if user has admin rights for the selected room's owner (in case it changed)
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (! $user->is_global_admin && ! $user->isAdminOf($room->owner)) {
            return redirect()->route('room-discounts.index')
                ->with('error', __('You do not have administration rights for the new owner.'));
        }

        // Update discount
        $roomDiscount->update($validated);

        return redirect()->route('room-discounts.index')
            ->with('success', __('Discount updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomDiscount $roomDiscount): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has admin rights for this discount's room's owner
        if (! $user->isAdminOf($roomDiscount->room->owner)) {
            return redirect()->route('room-discounts.index')
                ->with('error', __('You do not have administration rights for this discount.'));
        }

        $roomDiscount->delete();

        return redirect()->route('room-discounts.index')
            ->with('success', __('Discount deleted successfully.'));
    }
}
