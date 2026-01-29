@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="auth-container container-full-form">
    <h1>Réinitialiser le mot de passe</h1>

    @if($errors->any())
        <div class="error-messages">
            @foreach($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="styled-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <div class="form-element">
                <label for="email" class="form-element-title">Adresse email</label>
                <div class="form-field">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-element">
                <label for="password" class="form-element-title">Nouveau mot de passe</label>
                <div class="form-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>
                <p class="text-sm text-gray-600 mt-1">Minimum 12 caractères</p>
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
            <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
        </div>
    </form>
</div>
@endsection
