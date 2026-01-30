@php
    $showPasswordFields = $showPasswordFields ?? true;
@endphp

<div class="form-group">
    <h3 class="form-group-title">Informations personnelles</h3>

    <fieldset class="form-element">
        <div class="form-field">
            <label for="name" class="form-element-title">Nom du compte</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user?->name) }}"
                required
            >
            @error('name')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </fieldset>

    <fieldset class="form-element">
        <div class="form-field">
            <label for="email" class="form-element-title">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user?->email) }}"
                required
            >
            @if(request()->routeIs('users.edit'))
                <small class="text-gray-600">L'email est automatiquement vérifié pour les comptes créé·e·s par un·e admin</small>
            @else
                <small class="text-gray-600">Si vous modifiez votre email, vous devrez le vérifier à nouveau</small>
            @endif
            @error('email')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </fieldset>
</div>

@if($showPasswordFields)
<div class="form-group">
    <h3 class="form-group-title">Modifier le mot de passe (optionnel)</h3>
    <fieldset class="form-element">
        <div class="form-field">
            <label for="password" class="form-element-title">
                Mot de passe
                @if(isset($user))
                    <span class="text-xs text-gray-500">(laisser vide pour ne pas changer)</span>
                @endif
            </label>
            <input
                type="password"
                id="password"
                name="password"
                @if(!isset($user)) required @endif
            >
            <small class="text-gray-600">Minimum 12 caractères</small>
            @error('password')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </fieldset>

    <fieldset class="form-element">
        <div class="form-field">
            <label for="password_confirmation" class="form-element-title">Confirmer le mot de passe</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                @if(!isset($user)) required @endif
            >
        </div>
    </fieldset>
</div>
@endif
