@extends('layouts.app')

@section('title', isset($unavailability) ? __('Edit unavailability') : __('New unavailability'))

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($unavailability) ? __('Edit unavailability') : __('New unavailability') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($unavailability) ? route('room-unavailabilities.update', $unavailability) : route('room-unavailabilities.store') }}" class="styled-form">
            @csrf
            @if(isset($unavailability))
                @method('PUT')
            @endif

            <!-- Informations de base -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Basic information') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="room_id" class="form-element-title">{{ __('Room') }}</label>
                        <select name="room_id" id="room_id" required>
                            <option value="">{{ __('Select a room') }}</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $unavailability?->room_id ?? $currentRoomId) == $room->id)>
                                    {{ $room->name }} ({{ $room->owner->contact->display_name() }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="title" class="form-element-title">{{ __('Title') }}</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $unavailability?->title) }}"
                            placeholder="{{ __('Unavailable') }}"
                        >
                        <small class="text-gray-600">{{ __('optional') }}</small>
                        @error('title')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <!-- Periode -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Period') }}</h3>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="start" class="form-element-title">{{ __('Start') }}</label>
                            <input
                                type="datetime-local"
                                id="start"
                                name="start"
                                value="{{ old('start', $unavailability?->startLocalTz()?->format('Y-m-d\TH:i')) }}"
                                required
                            >
                            @error('start')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="end" class="form-element-title">{{ __('End') }}</label>
                            <input
                                type="datetime-local"
                                id="end"
                                name="end"
                                value="{{ old('end', $unavailability?->endLocalTz()?->format('Y-m-d\TH:i')) }}"
                                required
                            >
                            @error('end')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ route('room-unavailabilities.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($unavailability) ? __('Update') : __('Create') }}
                </button>
                @if(isset($unavailability))
                    <button type="button" onclick="confirmDeleteUnavailability()" class="btn btn-delete">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </form>

        @if(isset($unavailability))
            <form id="delete-unavailability-form" action="{{ route('room-unavailabilities.destroy', $unavailability) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>

<script>
    function confirmDeleteUnavailability() {
        if (confirm('{{ __('Are you sure you want to delete this unavailability?') }}')) {
            document.getElementById('delete-unavailability-form').submit();
        }
    }
</script>
@endsection
