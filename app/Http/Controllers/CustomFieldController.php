<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\CustomField;
use App\Models\Room;
use App\Validation\CustomFieldRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    use RedirectsBack;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAnyCustomFields', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        // Build query
        $query = CustomField::with(['room.owner.contact'])
            ->whereIn('room_id', $roomIds);

        // Filter by room
        $currentRoomId = $request->input('room_id');
        if ($currentRoomId) {
            $query->where('room_id', $currentRoomId);
        }

        $query->orderBy(Room::select('name')->whereColumn('rooms.id', 'custom_fields.room_id'))->orderBy('label', 'asc');
        $customFields = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('custom-fields.index', [
            'customFields' => $customFields,
            'rooms' => $rooms,
            'currentRoomId' => $currentRoomId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('viewAnyCustomFields', Room::class);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('custom-fields.form', [
            'field' => null,
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
        $validated = $request->validate(CustomFieldRules::rules());

        // Security: authorize on the target room
        $room = Room::findOrFail($validated['room_id']);
        $this->authorize('manageCustomFields', $room);

        // Generate key from label
        $baseKey = 'cf_'.Str::slug($validated['label']);
        $validated['key'] = Str::limit($baseKey, 50, '');

        // Ensure key is unique for this room
        $counter = 1;
        while (CustomField::where('room_id', $validated['room_id'])->where('key', $validated['key'])->exists()) {
            $validated['key'] = Str::limit($baseKey.'-'.$counter, 50, '');
            $counter++;
        }

        // Process options if present (textarea with one option per line)
        if (! empty($validated['options'])) {
            $options = array_filter(
                array_map('trim', explode("\n", $validated['options'])),
                fn ($option) => $option !== ''
            );
            $validated['options'] = array_values($options);
        } else {
            $validated['options'] = null;
        }

        // Force required to false for select, checkbox, radio types
        if (in_array($validated['type'], ['select', 'checkbox', 'radio'])) {
            $validated['required'] = false;
        }

        // Create field
        $field = CustomField::create($validated);

        return $this->redirectBack('custom-fields.index')
            ->with('success', __('Custom field created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomField $customField): View
    {
        $this->authorize('manageCustomFields', $customField->room);

        $user = auth()->user();
        $roomIds = $user->getAccessibleRoomIds(UserRole::ADMIN);

        $rooms = Room::with('owner.contact')
            ->whereIn('id', $roomIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('custom-fields.form', [
            'field' => $customField,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomField $customField): RedirectResponse
    {
        $this->authorize('manageCustomFields', $customField->room);

        // Validate
        $validated = $request->validate(CustomFieldRules::rules($customField->id));

        // If room changed, check authorization on the new room too
        if ($validated['room_id'] != $customField->room_id) {
            $newRoom = Room::findOrFail($validated['room_id']);
            $this->authorize('manageCustomFields', $newRoom);
        }

        // Regenerate key from label if label changed
        if ($validated['label'] !== $customField->label) {
            $baseKey = 'cf_'.Str::slug($validated['label']);
            $validated['key'] = Str::limit($baseKey, 50, '');

            // Ensure key is unique for this room
            $counter = 1;
            while (CustomField::where('room_id', $validated['room_id'])
                ->where('key', $validated['key'])
                ->where('id', '!=', $customField->id)
                ->exists()) {
                $validated['key'] = Str::limit($baseKey.'-'.$counter, 50, '');
                $counter++;
            }
        }

        // Process options if present (textarea with one option per line)
        if (! empty($validated['options'])) {
            $options = array_filter(
                array_map('trim', explode("\n", $validated['options'])),
                fn ($option) => $option !== ''
            );
            $validated['options'] = array_values($options);
        } else {
            $validated['options'] = null;
        }

        // Force required to false for select, checkbox, radio types
        if (in_array($validated['type'], ['select', 'checkbox', 'radio'])) {
            $validated['required'] = false;
        }

        // Update field
        $customField->update($validated);

        return $this->redirectBack('custom-fields.index')
            ->with('success', __('Custom field updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $customField): RedirectResponse
    {
        $this->authorize('manageCustomFields', $customField->room);

        $customField->delete();

        return $this->redirectBack('custom-fields.index')
            ->with('success', __('Custom field deleted successfully.'));
    }
}
