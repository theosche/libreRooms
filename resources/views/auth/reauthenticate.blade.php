@extends('layouts.app')

@section('title', 'Confirmer votre identité')

@section('content')
<div class="max-w-4xl mx-auto py-6">
<div class="auth-container container-full-form">
    <h1>Confirmer votre identité</h1>
    <p class="text-gray-600 mb-6 text-center">Pour des raisons de sécurité, veuillez confirmer votre identité avant de continuer.</p>

    @if(session('error'))
        <div class="error-messages">
            <p class="error">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="error-messages">
            @foreach($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($hasPassword)
        <form method="POST" action="{{ route('reauthenticate.password') }}" class="styled-form">
            @csrf

            <div class="form-group">
                <div class="form-element">
                    <label for="email" class="form-element-title">Email</label>
                    <div class="form-field">
                        <input
                            type="email"
                            id="email"
                            value="{{ auth()->user()->email }}"
                            disabled
                            class="bg-gray-100"
                        >
                    </div>
                </div>

                <div class="form-element">
                    <label for="password" class="form-element-title">Mot de passe</label>
                    <div class="form-field">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Confirmer</button>
            </div>
        </form>
    @endif

    @if($hasPassword && $oidcProviders->isNotEmpty())
        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">OU</span>
            </div>
        </div>
    @endif

    @if($oidcProviders->isNotEmpty())
        <!-- OIDC Re-authentication Buttons -->
        <div class="styled-form">
            <div class="form-group">
            @foreach($oidcProviders as $provider)
                <a href="{{ route('reauthenticate.oidc.redirect', $provider) }}" class="btn btn-secondary w-full flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    Se reconnecter avec {{ $provider->name }}
                </a>
            @endforeach
            </div>
        </div>
    @endif

    <p class="auth-link mt-6">
        <a href="{{ route('profile') }}">Annuler et retourner au profil</a>
    </p>
</div>
</div>
@endsection
