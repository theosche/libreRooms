@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="auth-container container-full-form">
    <h1>Inscription</h1>

    @if($errors->any())
        <div class="error-messages">
            @foreach($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="styled-form">
        @csrf

        <div class="form-group">
            <div class="form-element">
                <label for="name" class="form-element-title">Nom</label>
                <div class="form-field">
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-element">
                <label for="email" class="form-element-title">Email</label>
                <div class="form-field">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
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
                    >
                </div>
            </div>

            <div class="form-element">
                <label for="password_confirmation" class="form-element-title">Confirmer le mot de passe</label>
                <div class="form-field">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                    >
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </div>
    </form>

    <p class="auth-link">
        Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
    </p>
</div>
@endsection
