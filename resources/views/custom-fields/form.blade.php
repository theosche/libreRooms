@extends('layouts.app')

@section('title', isset($field) ? __('Edit custom field') : __('New custom field'))

@section('page-script')
    @vite(['resources/js/custom-fields/custom-field-form.js'])
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($field) ? __('Edit custom field') : __('New custom field') }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($field) ? route('custom-fields.update', $field) : route('custom-fields.store') }}" class="styled-form">
            @csrf
            @if(isset($field))
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
                                <option value="{{ $room->id }}" @selected(old('room_id', $field?->room_id ?? $currentRoomId) == $room->id)>
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
                        <label for="label" class="form-element-title">{{ __('Field label') }}</label>
                        <input
                            type="text"
                            id="label"
                            name="label"
                            value="{{ old('label', $field?->label) }}"
                            required
                        >
                        <small class="text-gray-600">{{ __('The field name as it will appear in the form. The technical key will be generated automatically.') }}</small>
                        @error('label')
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
                                @checked(old('active', $field?->active ?? true))
                            >
                            <span>{{ __('Active field') }}</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration du champ -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('Field configuration') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="type" class="form-element-title">{{ __('Field type') }}</label>
                        <select name="type" id="type" required>
                            <option value="text" @selected(old('type', $field?->type?->value ?? 'text') == 'text')>{{ __('Short text') }}</option>
                            <option value="textarea" @selected(old('type', $field?->type?->value) == 'textarea')>{{ __('Long text (textarea)') }}</option>
                            <option value="select" @selected(old('type', $field?->type?->value) == 'select')>{{ __('Dropdown list (select)') }}</option>
                            <option value="checkbox" @selected(old('type', $field?->type?->value) == 'checkbox')>{{ __('Checkboxes') }}</option>
                            <option value="radio" @selected(old('type', $field?->type?->value) == 'radio')>{{ __('Radio buttons') }}</option>
                        </select>
                        @error('type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element" id="options_field">
                    <div class="form-field">
                        <label for="options" class="form-element-title">{{ __('Available options') }}</label>
                        <textarea
                            id="options"
                            name="options"
                            rows="5"
                            placeholder="{{ __('Enter one option per line') }}&#10;{{ __('For example:') }}&#10;Option 1&#10;Option 2&#10;Option 3"
                        >{{ old('options', isset($field) && $field->options ? implode("\n", $field->options) : '') }}</textarea>
                        <small class="text-gray-600">{{ __('Enter one option per line. Each line will become a selectable option.') }}</small>
                        @error('options')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input
                                type="hidden"
                                name="required"
                                value="0"
                            >
                            <input
                                type="checkbox"
                                id="required"
                                name="required"
                                value="1"
                                @checked(old('required', $field?->required))
                            >
                            <span>{{ __('Required field') }}</span>
                        </label>
                        <small class="text-gray-600">{{ __('Available only for "Short text" and "Long text" field types') }}</small>
                    </div>
                </fieldset>
            </div>

            <div class="btn-group justify-end mt-6">
                <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($field) ? __('Update') : __('Create') }}
                </button>
                @if(isset($field))
                    <button type="button" onclick="confirmDeleteField()" class="btn btn-delete">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </form>
        @if(isset($field))
            <form id="delete-field-form" action="{{ route('custom-fields.destroy', $field) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
<script>
    function confirmDeleteField() {
        if (confirm('{{ __('Are you sure you want to delete this custom field?') }}')) {
            document.getElementById('delete-field-form').submit();
        }
    }
</script>
@endsection
