@extends('layouts.app')

@section('title', isset($option) ? 'Modifier l\'option' : 'Nouvelle option')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ isset($option) ? 'Modifier l\'option' : 'Nouvelle option' }}
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
                <h3 class="form-group-title">Informations de base</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="room_id" class="form-element-title">Salle</label>
                        <select name="room_id" id="room_id" required>
                            <option value="">Sélectionner une salle</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $option?->room_id) == $room->id)>
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
                        <label for="name" class="form-element-title">Nom de l'option</label>
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
                        <label for="price" class="form-element-title">Prix</label>
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
                            <span>Option active</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('room-options.index') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($option) ? 'Mettre à jour' : 'Créer' }}
                </button>
                @if(isset($option))
                    <button type="button" onclick="confirmDeleteOption()" class="btn btn-delete">
                        Supprimer
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
        if (confirm('Êtes-vous sûr de vouloir supprimer cette option ?')) {
            document.getElementById('delete-option-form').submit();
        }
    }
</script>
@endsection
