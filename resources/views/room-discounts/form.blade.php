@extends('layouts.app')

@section('title', isset($discount) ? 'Modifier la réduction' : 'Nouvelle réduction')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ isset($discount) ? 'Modifier la réduction' : 'Nouvelle réduction' }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($discount) ? route('room-discounts.update', $discount) : route('room-discounts.store') }}" class="styled-form">
            @csrf
            @if(isset($discount))
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
                                <option value="{{ $room->id }}" @selected(old('room_id', $discount?->room_id) == $room->id)>
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
                        <label for="name" class="form-element-title">Nom de la réduction</label>
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
                        <label for="description" class="form-element-title">Description</label>
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
                            <span>Réduction active</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration de la réduction -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration de la réduction</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="type" class="form-element-title">Type de réduction</label>
                        <select name="type" id="type" required>
                            <option value="fixed" @selected(old('type', $discount?->type?->value ?? 'fixed') == 'fixed')>Montant fixe</option>
                            <option value="percent" @selected(old('type', $discount?->type?->value) == 'percent')>Pourcentage</option>
                        </select>
                        @error('type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="value" class="form-element-title">Valeur</label>
                        <input
                            type="number"
                            id="value"
                            name="value"
                            step="0.01"
                            min="0"
                            value="{{ old('value', $discount?->value ?? '') }}"
                            required
                        >
                        <small class="text-gray-600">Montant fixe ou pourcentage selon sélection</small>
                        @error('value')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="limit_to_contact_type" class="form-element-title">Limiter au type de contact</label>
                        <select name="limit_to_contact_type" id="limit_to_contact_type">
                            <option value="">Tous les types</option>
                            <option value="individual" @selected(old('limit_to_contact_type', $discount?->limit_to_contact_type?->value) == 'individual')>Privé·e uniquement</option>
                            <option value="organization" @selected(old('limit_to_contact_type', $discount?->limit_to_contact_type?->value) == 'organization')>Organisation uniquement</option>
                        </select>
                        <small class="text-gray-600">Optionnel : restreindre la réduction à un type de contact spécifique</small>
                        @error('limit_to_contact_type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('room-discounts.index') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($discount) ? 'Mettre à jour' : 'Créer' }}
                </button>
                @if(isset($discount))
                    <button type="button" onclick="confirmDeleteDiscount()" class="btn btn-delete">
                        Supprimer
                    </button>
                @endif
            </div>
        </form>

        @if(isset($discount))
            <form id="delete-discount-form" action="{{ route('room-discounts.destroy', $discount) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>

<script>
    function confirmDeleteDiscount() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette réduction ?')) {
            document.getElementById('delete-discount-form').submit();
        }
    }
</script>
@endsection
