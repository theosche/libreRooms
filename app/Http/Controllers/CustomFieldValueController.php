<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

class CustomFieldValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customFieldValues = CustomFieldValue::with(['reservation', 'customField'])
            ->paginate(15);

        return response()->json($customFieldValues);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'custom_field_id' => 'required|exists:custom_fields,id',
            'value' => 'required|string',
        ]);

        $customFieldValue = CustomFieldValue::create($validated);

        $customFieldValue->load(['reservation', 'customField']);

        return response()->json($customFieldValue, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomFieldValue $customFieldValue)
    {
        $customFieldValue->load(['reservation', 'customField']);

        return response()->json($customFieldValue);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomFieldValue $customFieldValue)
    {
        $validated = $request->validate([
            'reservation_id' => 'sometimes|exists:reservations,id',
            'custom_field_id' => 'sometimes|exists:custom_fields,id',
            'value' => 'sometimes|string',
        ]);

        $customFieldValue->update($validated);

        $customFieldValue->load(['reservation', 'customField']);

        return response()->json($customFieldValue);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomFieldValue $customFieldValue)
    {
        $customFieldValue->delete();

        return response()->json(null, 204);
    }
}
