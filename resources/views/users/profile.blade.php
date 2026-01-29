@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Mon profil</h1>
            <p class="mt-2 text-sm text-gray-600">Gérez vos informations personnelles</p>
        </div>

        @if(!$user->email_verified_at)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Votre email n'est pas encore vérifié. Veuillez vérifier votre boîte email et cliquer sur le
                            lien de vérification.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="styled-form">
                @csrf
                @method('PUT')

                @include('users.partials._basic_fields', [
                    'user' => $user,
                    'showPasswordFields' => false,
                ])

                <div class="form-group">
                    <h3 class="form-group-title">Sécurité</h3>
                    <fieldset class="form-element">
                        <div class="form-field">
                            <a href="{{ route('profile.password') }}" class="link-primary">
                                {{ auth()->user()->password ? 'Modifier le mot de passe' : 'Définir un mot de passe' }}
                            </a>
                            @if(!auth()->user()->password)
                                <small class="text-gray-600">Définir un mot de passe vous permettra de vous connecter avec votre email</small>
                            @endif
                        </div>
                    </fieldset>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Mettre à jour
                    </button>
                    <button type="button" onclick="confirmDeleteAccount()"
                            class="btn bg-red-600 hover:bg-red-700 text-white">
                        Supprimer mon compte
                    </button>
                </div>
            </form>

            <form id="delete-account-form" action="{{ route('profile.delete') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        function confirmDeleteAccount() {
            if (confirm('Êtes-vous absolument sûr de vouloir supprimer votre compte ? Cette action est irréversible et toutes vos données seront perdues.')) {
                document.getElementById('delete-account-form').submit();
            }
        }
    </script>
@endsection
