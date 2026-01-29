@extends('layouts.app')

@section('title', isset($field) ? 'Modifier le champ personnalisé' : 'Nouveau champ personnalisé')

@section('page-script')
    @vite(['resources/js/custom-fields/custom-field-form.js'])
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ isset($field) ? 'Modifier le champ personnalisé' : 'Nouveau champ personnalisé' }}
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
                <h3 class="form-group-title">Informations de base</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="room_id" class="form-element-title">Salle</label>
                        <select name="room_id" id="room_id" required>
                            <option value="">Sélectionner une salle</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id', $field?->room_id) == $room->id)>
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
                        <label for="label" class="form-element-title">Label du champ</label>
                        <input
                            type="text"
                            id="label"
                            name="label"
                            value="{{ old('label', $field?->label) }}"
                            required
                        >
                        <small class="text-gray-600">Le nom du champ tel qu'il apparaîtra dans le formulaire. La clé technique sera générée automatiquement.</small>
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
                            <span>Champ actif</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration du champ -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration du champ</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="type" class="form-element-title">Type de champ</label>
                        <select name="type" id="type" required>
                            <option value="text" @selected(old('type', $field?->type?->value ?? 'text') == 'text')>Texte court</option>
                            <option value="textarea" @selected(old('type', $field?->type?->value) == 'textarea')>Texte long (textarea)</option>
                            <option value="select" @selected(old('type', $field?->type?->value) == 'select')>Liste déroulante (select)</option>
                            <option value="checkbox" @selected(old('type', $field?->type?->value) == 'checkbox')>Cases à cocher (checkbox)</option>
                            <option value="radio" @selected(old('type', $field?->type?->value) == 'radio')>Boutons radio</option>
                        </select>
                        @error('type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element" id="options_field">
                    <div class="form-field">
                        <label for="options" class="form-element-title">Options disponibles</label>
                        <textarea
                            id="options"
                            name="options"
                            rows="5"
                            placeholder="Entrez une option par ligne&#10;Par exemple :&#10;Option 1&#10;Option 2&#10;Option 3"
                        >{{ old('options', isset($field) && $field->options ? implode("\n", $field->options) : '') }}</textarea>
                        <small class="text-gray-600">Entrez une option par ligne. Chaque ligne deviendra une option sélectionnable.</small>
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
                            <span>Champ requis</span>
                        </label>
                        <small class="text-gray-600">Disponible uniquement pour les champs de type "Texte court" et "Texte long"</small>
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($field) ? 'Mettre à jour' : 'Créer' }}
                </button>
                @if(isset($field))
                    <button type="button" onclick="confirmDeleteField()" class="btn btn-delete">
                        Supprimer
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
        if (confirm('Êtes-vous sûr de vouloir supprimer ce champ personnalisé ?')) {
            document.getElementById('delete-field-form').submit();
        }
    }
</script>
@endsection
