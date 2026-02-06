@extends('layouts.app')

@section('title', isset($option) ? __('Edit option') : __('New option'))

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($option) ? __('Edit option') : __('New option') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($option) ? route('room-options.update', $option) : route('room-options.store') }}" class="styled-form">
            @csrf
            @if(isset($option))
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
                                <option value="{{ $room->id }}" @selected(old('room_id', $option?->room_id ?? $currentRoomId) == $room->id)>
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
                        <label for="name" class="form-element-title">{{ __('Option name') }}</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $option?->name) }}"
                            required
                        >
                        @error('name')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="description" class="form-element-title">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                        >{{ old('description', $option?->description) }}</textarea>
                        @error('description')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="price" class="form-element-title">{{ __('Price') }}</label>
                        <input
                            type="number"
                            id="price"
                            name="price"
                            step="0.01"
                            min="0"
                            value="{{ old('price', $option?->price ?? '') }}"
                            required
                        >
                        @error('price')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input
                                type="hidden"
                                name="active"
                                value="0"
                            >
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                @checked(old('active', $option?->active ?? true))
                            >
                            <span>{{ __('Active option') }}</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ route('room-options.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($option) ? __('Update') : __('Create') }}
                </button>
                @if(isset($option))
                    <button type="button" onclick="confirmDeleteOption()" class="btn btn-delete">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </form>
        @if(isset($option))
            <form id="delete-option-form" action="{{ route('room-options.destroy', $option) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
<script>
    function confirmDeleteOption() {
        if (confirm('{{ __('Are you sure you want to delete this option?') }}')) {
            document.getElementById('delete-option-form').submit();
        }
    }
</script>
@endsection
