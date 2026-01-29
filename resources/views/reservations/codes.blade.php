@extends('layouts.app')

@section('title', 'Codes d\'accès - ' . $room->name)

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
            <h1 class="text-2xl font-bold mb-2">Codes d'accès</h1>
            <p class="opacity-90">{{ $room->name }}</p>
        </div>

        <!-- Reservation Info -->
        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex flex-wrap gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Réservation :</span>
                    <span class="font-medium text-gray-900">{{ $reservation->title }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Contact :</span>
                    <span class="font-medium text-gray-900">{{ $reservation->tenant->display_name() }}</span>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-gray-500 text-sm">Dates :</span>
                <div class="mt-1 space-y-1">
                    @foreach($reservation->events as $event)
                        <div class="text-sm font-medium text-gray-900">
                            {{ $event->startLocalTz()->format('d.m.Y') }} de {{ $event->startLocalTz()->format('H:i') }} à {{ $event->endLocalTz()->format('H:i') }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Secret Message -->
        <div class="px-6 py-6">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-medium">Information importante</p>
                        <p class="mt-1">Ces codes peuvent être modifiés à tout moment. Nous vous recommandons de consulter cette page le jour même de votre événement pour vous assurer d'avoir les informations à jour.</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Codes d'accès
                </h2>
                <div class="text-gray-100 whitespace-pre-wrap font-mono text-sm leading-relaxed">{{ $room->secret_message }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 text-center">
            <p class="text-sm text-gray-600">
                Pour toute question, contactez-nous à
                <a href="mailto:{{ $room->owner->contact->email }}" class="text-blue-600 hover:text-blue-800">
                    {{ $room->owner->contact->email }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
