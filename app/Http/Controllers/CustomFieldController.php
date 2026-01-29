<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Owner;
use App\Models\Room;
use App\Validation\CustomFieldRules;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
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
        $query = CustomField::with(['room.owner.contact'])
            ->whereHas('room', function ($q) use ($ownerIds) {
                $q->whereIn('owner_id', $ownerIds);
            });

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $query->orderBy('room_id', 'asc')->orderBy('label', 'asc');
        $customFields = $query->paginate(15)->appends($request->except('page'));

        // Get available rooms for filters
        $rooms = \App\Models\Room::with('owner.contact')
            ->whereIn('owner_id', $ownerIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('custom-fields.index', [
            'customFields' => $customFields,
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
            abort(403, 'Vous devez être administrateur d\'au moins un propriétaire pour créer un champ personnalisé.');
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

        return view('custom-fields.form', [
            'field' => null,
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
        $validated = $request->validate(CustomFieldRules::rules());

        // Check if user has admin rights for the selected room's owner
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (!$user->is_global_admin && !$user->isAdminOf($room->owner)) {
            return redirect()->route('custom-fields.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour ce propriétaire.');
        }

        // Generate key from label
        $baseKey = 'cf_' . Str::slug($validated['label']);
        $validated['key'] = Str::limit($baseKey, 50, '');

        // Ensure key is unique for this room
        $counter = 1;
        while (CustomField::where('room_id', $validated['room_id'])->where('key', $validated['key'])->exists()) {
            $validated['key'] = Str::limit($baseKey . '-' . $counter, 50, '');
            $counter++;
        }

        // Process options if present (textarea with one option per line)
        if (!empty($validated['options'])) {
            $options = array_filter(
                array_map('trim', explode("\n", $validated['options'])),
                fn($option) => $option !== ''
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

        return redirect()->route('custom-fields.index')
            ->with('success', 'Le champ personnalisé a été créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomField $customField): View
    {
        $user = auth()->user();

        // Check if user has admin rights for this field's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($customField->room->owner)) {
            abort(403, 'Vous n\'avez pas les droits d\'administration pour ce champ personnalisé.');
        }

        // Get rooms where user has admin rights
        if ($user->is_global_admin) {
            $rooms = Room::with('owner.contact')->orderBy('name', 'asc')->get();
        } else {
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $rooms = Room::with('owner.contact')->whereIn('owner_id', $ownerIds)->orderBy('name', 'asc')->get();
        }

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
        $user = auth()->user();

        // Check if user has admin rights for this field's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($customField->room->owner)) {
            return redirect()->route('custom-fields.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour ce champ personnalisé.');
        }

        // Validate
        $validated = $request->validate(CustomFieldRules::rules($customField->id));

        // Check if user has admin rights for the selected room's owner (in case it changed)
        $room = Room::with('owner')->findOrFail($validated['room_id']);
        if (!$user->is_global_admin && !$user->isAdminOf($room->owner)) {
            return redirect()->route('custom-fields.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour le nouveau propriétaire.');
        }

        // Regenerate key from label if label changed
        if ($validated['label'] !== $customField->label) {
            $baseKey = 'cf_' . Str::slug($validated['label']);
            $validated['key'] = Str::limit($baseKey, 50, '');

            // Ensure key is unique for this room
            $counter = 1;
            while (CustomField::where('room_id', $validated['room_id'])
                ->where('key', $validated['key'])
                ->where('id', '!=', $customField->id)
                ->exists()) {
                $validated['key'] = Str::limit($baseKey . '-' . $counter, 50, '');
                $counter++;
            }
        }

        // Process options if present (textarea with one option per line)
        if (!empty($validated['options'])) {
            $options = array_filter(
                array_map('trim', explode("\n", $validated['options'])),
                fn($option) => $option !== ''
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

        return redirect()->route('custom-fields.index')
            ->with('success', 'Le champ personnalisé a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $customField): RedirectResponse
    {
        $user = auth()->user();

        // Check if user has admin rights for this field's room's owner
        if (!$user->is_global_admin && !$user->isAdminOf($customField->room->owner)) {
            return redirect()->route('custom-fields.index')
                ->with('error', 'Vous n\'avez pas les droits d\'administration pour ce champ personnalisé.');
        }

        $customField->delete();

        return redirect()->route('custom-fields.index')
            ->with('success', 'Le champ personnalisé a été supprimé avec succès.');
    }
}