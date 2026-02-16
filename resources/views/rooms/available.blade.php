@extends('layouts.app')

@section('title', __('Current availability') . ' - ' . $room->name)

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header with status -->
        @php
            $bgColor = match($status->value) {
                'free' => 'from-green-600 to-green-700',
                'occupied' => 'from-red-600 to-red-700',
                'unavailable' => 'from-orange-500 to-orange-600',
                'outside_hours' => 'from-gray-500 to-gray-600',
            };
        @endphp
        <div class="bg-gradient-to-r {{ $bgColor }} px-6 py-8 text-white">
            <h1 class="text-2xl font-bold mb-2">{{ $room->name }}</h1>
            <div class="flex items-center gap-3 mt-4">
                @if($status === \App\Enums\RoomCurrentStatus::FREE)
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($status === \App\Enums\RoomCurrentStatus::OCCUPIED)
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($status === \App\Enums\RoomCurrentStatus::UNAVAILABLE)
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
                <span class="text-2xl font-semibold">{{ $status->label() }}</span>
            </div>
        </div>

        <!-- Main content -->
        <div class="px-6 py-6">
            <!-- Current time -->
            <p class="text-sm text-gray-500 mb-6">
                {{ __('Updated at') }} {{ $now->format('H:i') }} ({{ $now->format('d.m.Y') }})
            </p>

            @if($status === \App\Enums\RoomCurrentStatus::FREE)
                <!-- Free status -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-green-900">{{ __('The room is currently available') }}</h3>
                            @if($freeUntil)
                                <p class="text-green-700 mt-1">
                                    {{ __('Free until :time', ['time' => $freeUntil->format('H:i')]) }}
                                    @if(!$freeUntil->isToday())
                                        ({{ $freeUntil->format('d.m.Y') }})
                                    @endif
                                </p>
                            @else
                                <p class="text-green-700 mt-1">{{ __('No upcoming reservation') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

            @elseif($status === \App\Enums\RoomCurrentStatus::OCCUPIED)
                <!-- Occupied status -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-red-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-900">{{ __('The room is currently occupied') }}</h3>

                            @if($eventInfo)
                                <div class="mt-3 space-y-2 text-sm">
                                    @if(isset($eventInfo['title']))
                                        <p class="text-red-800">
                                            <span class="font-medium">{{ __('Event') }}:</span> {{ $eventInfo['title'] }}
                                        </p>
                                    @endif
                                    <p class="text-red-700">
                                        <span class="font-medium">{{ __('Until') }}:</span> {{ $eventInfo['end']->format('H:i') }}
                                        @if(!$eventInfo['end']->isToday())
                                            ({{ $eventInfo['end']->format('d.m.Y') }})
                                        @endif
                                    </p>
                                    @if(isset($eventInfo['tenant']))
                                        <p class="text-red-700">
                                            <span class="font-medium">{{ __('Contact') }}:</span> {{ $eventInfo['tenant'] }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if($freeFrom)
                                <p class="mt-4 text-red-800 font-medium">
                                    {{ __('Available from :time', ['time' => $freeFrom->format('H:i')]) }}
                                    @if(!$freeFrom->isToday())
                                        ({{ $freeFrom->format('d.m.Y') }})
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            @elseif($status === \App\Enums\RoomCurrentStatus::UNAVAILABLE)
                <!-- Unavailable status -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-orange-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-orange-900">{{ __('The room is currently unavailable') }}</h3>

                            @if($currentUnavailability)
                                <div class="mt-3 space-y-2 text-sm">
                                    @if($currentUnavailability->title)
                                        <p class="text-orange-800">
                                            <span class="font-medium">{{ __('Reason') }}:</span> {{ $currentUnavailability->title }}
                                        </p>
                                    @endif
                                    <p class="text-orange-700">
                                        <span class="font-medium">{{ __('Until') }}:</span>
                                        {{ $currentUnavailability->endLocalTz()->format('H:i') }}
                                        @if(!$currentUnavailability->endLocalTz()->isToday())
                                            ({{ $currentUnavailability->endLocalTz()->format('d.m.Y') }})
                                        @endif
                                    </p>
                                </div>
                            @endif

                            @if($freeFrom)
                                <p class="mt-4 text-orange-800 font-medium">
                                    {{ __('Available from :time', ['time' => $freeFrom->format('H:i')]) }}
                                    @if(!$freeFrom->isToday())
                                        ({{ $freeFrom->format('d.m.Y') }})
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            @else
                <!-- Outside bookable hours -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="bg-gray-200 rounded-full p-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ __('Outside bookable hours') }}</h3>

                            @if(!empty($bookableHoursInfo))
                                <div class="mt-3 space-y-2 text-sm text-gray-700">
                                    @if(isset($bookableHoursInfo['days']))
                                        <p>
                                            <span class="font-medium">{{ __('Bookable days') }}:</span>
                                            {{ implode(', ', $bookableHoursInfo['days']) }}
                                        </p>
                                    @endif
                                    @if(isset($bookableHoursInfo['hours']))
                                        <p>
                                            <span class="font-medium">{{ __('Bookable hours') }}:</span>
                                            {{ $bookableHoursInfo['hours']['start'] }} - {{ $bookableHoursInfo['hours']['end'] }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if($freeFrom)
                                <p class="mt-4 text-gray-800 font-medium">
                                    {{ __('Available from :time', ['time' => $freeFrom->format('H:i')]) }}
                                    @if(!$freeFrom->isToday())
                                        ({{ $freeFrom->format('d.m.Y') }})
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer with link -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex flex-col sm:flex-row gap-3 justify-between items-center">
                <a href="{{ route('rooms.show', $room) }}" class="btn btn-primary">
                    {{ __('More info') }}
                </a>
                @can('reserve', $room)
                    <a href="{{ route('reservations.create', $room) }}" class="btn btn-secondary">
                        {{ __('Book this room') }}
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
