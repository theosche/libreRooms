@extends('layouts.app')

@section('title', isset($provider) ? 'Modifier le fournisseur d\'identité' : 'Nouveau fournisseur d\'identité')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ isset($provider) ? 'Modifier le fournisseur d\'identité' : 'Nouveau fournisseur d\'identité' }}
        </h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ isset($provider) ? route('identity-providers.update', $provider) : route('identity-providers.store') }}" class="styled-form">
            @csrf
            @if(isset($provider))
                @method('PUT')
            @endif

            <!-- Informations de base -->
            <div class="form-group">
                <h3 class="form-group-title">Informations de base</h3>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="name" class="form-element-title">Nom</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $provider?->name) }}"
                                required
                                placeholder="ex: Nextcloud"
                            >
                            @error('name')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="driver" class="form-element-title">Driver</label>
                            <select name="driver" id="driver" required>
                                <option value="">Sélectionnez un driver</option>
                                @foreach(App\Enums\IdentityProviderDrivers::cases() as $driver)
                                    <option value="{{ $driver->value }}" @selected(old('driver', $provider?->driver) == $driver->value)>
                                        {{ $driver->value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="enabled" value="0">
                            <input
                                type="checkbox"
                                id="enabled"
                                name="enabled"
                                value="1"
                                @checked(old('enabled', $provider?->enabled ?? true))
                            >
                            <span class="font-medium">Actif</span>
                        </label>
                        <small class="text-gray-600">Les utilisateurs pourront se connecter via ce fournisseur</small>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration OIDC -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration OIDC</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="issuer_url" class="form-element-title">URL de l'issuer</label>
                        <input
                            type="url"
                            id="issuer_url"
                            name="issuer_url"
                            value="{{ old('issuer_url', $provider?->issuer_url) }}"
                            required
                            placeholder="https://cloud.example.com"
                        >
                        <small class="text-gray-600">URL de base du fournisseur d'identité</small>
                        @error('issuer_url')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="client_id" class="form-element-title">Client ID</label>
                        <input
                            type="text"
                            id="client_id"
                            name="client_id"
                            value="{{ old('client_id', $provider?->client_id) }}"
                            required
                        >
                        @error('client_id')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="client_secret" class="form-element-title">
                            Client Secret
                            @if(isset($provider) && $provider->client_secret)
                                <span class="text-xs text-gray-500">(laisser vide pour garder inchangé)</span>
                            @endif
                        </label>
                        <input
                            type="password"
                            id="client_secret"
                            name="client_secret"
                            value="{{ old('client_secret') }}"
                            {{ isset($provider) && $provider->client_secret ? 'placeholder=****************' : 'required' }}
                        >
                        @error('client_secret')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('identity-providers.index') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($provider) ? 'Mettre à jour' : 'Créer' }}
                </button>
                @if(isset($provider))
                    <button type="button" onclick="confirmDeleteProvider()" class="btn btn-delete">
                        Supprimer
                    </button>
                @endif
            </div>
        </form>
        @if(isset($provider))
            <form id="delete-provider-form" action="{{ route('identity-providers.destroy', $provider) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
@if(isset($provider))
<script>
    function confirmDeleteProvider() {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur d\'identité ? Les utilisateurs liés ne pourront plus se connecter via ce fournisseur.')) {
            document.getElementById('delete-provider-form').submit();
        }
    }
</script>
@endif
@endsection
