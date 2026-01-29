<?php

namespace App\Http\Controllers;

use App\Models\ReservationEvent;
use Illuminate\Http\Request;

class ReservationEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = ReservationEvent::with(['reservation', 'options'])
            ->paginate(15);

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'uid' => 'required|string',
            'price' => 'required|numeric|min:0',
            'price_label' => 'required|string',
        ]);

        $event = ReservationEvent::create($validated);

        if ($request->has('option_ids')) {
            foreach ($request->option_ids as $optionData) {
                $event->options()->attach($optionData['id'], [
                    'price' => $optionData['price'],
                ]);
            }
        }

        $event->load(['reservation', 'options']);

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ReservationEvent $reservationEvent)
    {
        $reservationEvent->load(['reservation', 'options']);

        return response()->json($reservationEvent);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReservationEvent $reservationEvent)
    {
        $validated = $request->validate([
            'reservation_id' => 'sometimes|exists:reservations,id',
            'start' => 'sometimes|date',
            'end' => 'sometimes|date|after:start',
            'uid' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'price_label' => 'sometimes|string',
        ]);

        $reservationEvent->update($validated);

        if ($request->has('option_ids')) {
            $reservationEvent->options()->detach();
            foreach ($request->option_ids as $optionData) {
                $reservationEvent->options()->attach($optionData['id'], [
                    'price' => $optionData['price'],
                ]);
            }
        }

        $reservationEvent->load(['reservation', 'options']);

        return response()->json($reservationEvent);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReservationEvent $reservationEvent)
    {
        $reservationEvent->delete();

        return response()->json(null, 204);
    }
}
