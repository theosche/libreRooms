@extends('layouts.app')

@section('title', __('Access codes') . ' - ' . $room->name)

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
            <h1 class="text-2xl font-bold mb-2">{{ __('Access codes') }}</h1>
            <p class="opacity-90">{{ $room->name }}</p>
        </div>

        <!-- Reservation Info -->
        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex flex-wrap gap-4 text-sm">
                <div>
                    <span class="text-gray-500">{{ __('Reservation') }}:</span>
                    <span class="font-medium text-gray-900">{{ $reservation->title }}</span>
                </div>
                <div>
                    <span class="text-gray-500">{{ __('Contact') }}:</span>
                    <span class="font-medium text-gray-900">{{ $reservation->tenant->display_name() }}</span>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-gray-500 text-sm">{{ __('Dates') }}:</span>
                <div class="mt-1 space-y-1">
                    @foreach($reservation->events as $event)
                        <div class="text-sm font-medium text-gray-900">
                            {{ $event->startLocalTz()->format('d.m.Y') }} de {{ $event->startLocalTz()->format('H:i') }} à {{ $event->endLocalTz()->format('H:i') }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Secret Message or Info Message -->
        <div class="px-6 py-6">
            @if($canView)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">{{ __('Important information') }}</p>
                            <p class="mt-1">{{ __('These codes may be changed at any time. We recommend checking this page on the day of your event to ensure you have the latest information.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        {{ __('Access codes') }}
                    </h2>
                    <div class="text-gray-100 whitespace-pre-wrap font-mono text-sm leading-relaxed">{{ $room->secret_message }}</div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="text-blue-800 font-medium">{{ $message }}</p>
                    @if($availableFrom)
                        <p class="text-blue-600 mt-2 text-sm">
                            {{ __('Access codes will be available from :date.', ['date' => $availableFrom->format('d.m.Y')]) }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 text-center">
            <p class="text-sm text-gray-600">
                {{ __('For any questions, contact us at') }}
                <a href="mailto:{{ $room->owner->contact->email }}" class="text-blue-600 hover:text-blue-800">
                    {{ $room->owner->contact->email }}
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
