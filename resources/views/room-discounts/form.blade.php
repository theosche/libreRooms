@extends('layouts.app')

@section('title', isset($discount) ? __('Edit discount') : __('New discount'))

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($discount) ? __('Edit discount') : __('New discount') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($discount) ? route('room-discounts.update', [$discount] + redirect_back_query()) : route('room-discounts.store', redirect_back_query()) }}" class="styled-form">
            @csrf
            @if(isset($discount))
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
                                <option value="{{ $room->id }}" @selected(old('room_id', $discount?->room_id ?? $currentRoomId) == $room->id)>
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
                        <label for="name" class="form-element-title">{{ __('Discount name') }}</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $discount?->name) }}"
                            required
                        >
                        @error('name')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="description" class="form-element-title">{{ __('Description') }}</label>
                        <input
                            type="text"
                            id="description"
                            name="description"
                            value="{{ old('description', $discount?->description) }}"
                        >
                        @error('description')
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
                                @checked(old('active', $discount?->active ?? true))
                            >
                            <span>{{ __('Active discount') }}</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration de la réduction -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Discount configuration') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="type" class="form-element-title">{{ __('Discount type') }}</label>
                        <select name="type" id="type" required>
                            <option value="fixed" @selected(old('type', $discount?->type?->value ?? 'fixed') == 'fixed')>{{ __('Fixed amount') }}</option>
                            <option value="percent" @selected(old('type', $discount?->type?->value) == 'percent')>{{ __('Percentage') }}</option>
                        </select>
                        @error('type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="value" class="form-element-title">{{ __('Value') }}</label>
                        <input
                            type="number"
                            id="value"
                            name="value"
                            step="0.01"
                            min="0"
                            value="{{ old('value', $discount?->value ?? '') }}"
                            required
                        >
                        <small class="text-gray-600">{{ __('Fixed amount or percentage depending on selection') }}</small>
                        @error('value')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="limit_to_contact_type" class="form-element-title">{{ __('Limit to contact type') }}</label>
                        <select name="limit_to_contact_type" id="limit_to_contact_type">
                            <option value="">{{ __('All types') }}</option>
                            <option value="individual" @selected(old('limit_to_contact_type', $discount?->limit_to_contact_type?->value) == 'individual')>{{ __('Individual only') }}</option>
                            <option value="organization" @selected(old('limit_to_contact_type', $discount?->limit_to_contact_type?->value) == 'organization')>{{ __('Organization only') }}</option>
                        </select>
                        <small class="text-gray-600">{{ __('Optional: restrict the discount to a specific contact type') }}</small>
                        @error('limit_to_contact_type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ redirect_back_url('room-discounts.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($discount) ? __('Update') : __('Create') }}
                </button>
                @if(isset($discount))
                    <button type="button" onclick="confirmDeleteDiscount()" class="btn btn-delete">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </form>

        @if(isset($discount))
            <form id="delete-discount-form" action="{{ route('room-discounts.destroy', [$discount] + redirect_back_query()) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>

<script>
    function confirmDeleteDiscount() {
        if (confirm('{{ __('Are you sure you want to delete this discount?') }}')) {
            document.getElementById('delete-discount-form').submit();
        }
    }
</script>
@endsection
