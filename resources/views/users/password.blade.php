@extends('layouts.app')

@section('title', $user->password ? 'Modifier le mot de passe' : 'Définir un mot de passe')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ $user->password ? 'Modifier le mot de passe' : 'Définir un mot de passe' }}
        </h1>
        <p class="mt-2 text-sm text-gray-600">
            @if($user->password)
                Choisissez un nouveau mot de passe sécurisé pour votre compte.
            @else
                Définissez un mot de passe pour pouvoir vous connecter avec votre email.
            @endif
        </p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('profile.password.update') }}" class="styled-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                Après avoir changé votre mot de passe, vous serez déconnecté de toutes vos autres sessions actives.
                            </p>
                        </div>
                    </div>
                </div>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="password" class="form-element-title">Nouveau mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autofocus
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
                            required
                        >
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <a href="{{ route('profile') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $user->password ? 'Modifier le mot de passe' : 'Définir le mot de passe' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
