@extends('layouts.app')

@section('title', 'Calendrier - ' . $room->name)

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Calendrier - {{ $room->name }}</h1>
                <p class="mt-2 text-sm text-gray-600">
                    @if($isAdmin)
                        <span class="text-green-600 font-medium">Mode administrateur</span> - Vous voyez toutes les informations
                    @else
                        @switch($room->calendar_view_mode->value)
                            @case('full')
                                Vous voyez toutes les informations des réservations
                                @break
                            @case('title')
                                Vous voyez les titres des réservations
                                @break
                            @case('slot')
                                Vous voyez uniquement les créneaux occupés
                                @break
                        @endswitch
                    @endif
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                    Retour aux salles
                </a>
                <a href="{{ route('reservations.create', $room) }}" class="btn btn-primary">
                    Réserver cette salle
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @include('rooms._calendar', ['room' => $room])
    </div>
</div>
@endsection