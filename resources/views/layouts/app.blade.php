<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LibreRooms')</title>
    @vite('resources/css/app.css')
    @yield('page-script')
    @vite(['resources/js/app.js'])
</head>
<body>
<header class="header">
    <nav class="nav">
        <a href="/" class="nav-logo">{{ config('app.name') }}</a>
        <div class="nav-menu">
            @auth
                <a href="{{ route('reservations.index') }}" class="nav-link">Réservations</a>
                <a href="{{ route('contacts.index') }}" class="nav-link">Contacts</a>
                <a href="{{ route('invoices.index') }}" class="nav-link">Factures</a>
            @endauth
            <a href="{{ route('rooms.index') }}" class="nav-link">Salles</a>
            @auth
                @can('viewany', App\Models\Owner::class)
                    <a href="{{ route('owners.index') }}" class="nav-link">Propriétaires</a>
                @endcan
                @if(auth()->user()->is_global_admin)
                    <a href="{{ route('users.index') }}" class="nav-link">Utilisateurs</a>
                    <a href="{{ route('system-settings.edit') }}" class="nav-link" title="Réglages système">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
                <a href="{{ route('profile') }}"><span class="nav-user">{{ auth()->user()->name }}</span></a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-action logout">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-action login">Connexion</a>
                <a href="{{ route('register') }}" class="nav-action register">Inscription</a>
            @endauth
        </div>
    </nav>
</header>

@if(session('success'))
    <div id="flash-success" class="flash-message flash-success">
        <div class="flash-content">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

@if(session('error'))
    <div id="flash-error" class="flash-message flash-error">
        <div class="flash-content">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

<main class="container">
    @yield('content')
</main>
</body>
</html>
