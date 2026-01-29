@extends('layouts.app')

@section('title', 'Configuration de l\'environnement')

@section('content')
<div class="auth-container container-full-form" style="max-width: 600px;">
    <div class="form-header">
        <h1 class="form-title">Configuration de l'environnement</h1>
        <p class="form-subtitle">Configurez les paramètres de base de l'application</p>
    </div>

    @if($dbConnected === true)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-green-800">
                    <p class="font-medium">Base de données connectée</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-yellow-800">
                    <p class="font-medium mb-1">Base de données non configurée</p>
                    <p>Veuillez configurer la connexion à la base de données pour continuer.</p>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($setupError))
        <div class="error-messages">
            <p class="error">{{ $setupError }}</p>
        </div>
    @endif
    @php
        $protocol = (request()->secure() || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') ? 'https' : 'http';
    @endphp
    <form method="POST" action="{{ route('setup.environment.store') }}" class="styled-form">
        @csrf
        <div class="form-group">
            <h3 class="form-group-title">Application</h3>

            <fieldset class="form-element">
                <div class="form-field">
                    <label for="APP_URL" class="form-element-title">URL de l'application</label>
                    <input
                        type="url"
                        id="APP_URL"
                        name="APP_URL"
                        value="{{ old('APP_URL', !empty($config['APP_URL']) ? $config('APP_URL') : $protocol . "://" . $_SERVER['HTTP_HOST']) }}"
                        required
                        placeholder="https://reservations.example.com"
                    >
                    <small class="text-gray-600">L'URL publique de votre application (sans slash final)</small>

                </div>
            </fieldset>

            <fieldset class="form-element">
                <div class="form-field">
                    <label for="APP_LOCALE" class="form-element-title">Langue</label>
                    <select id="APP_LOCALE" name="APP_LOCALE" required>
                        @foreach($locales as $code => $name)
                            <option value="{{ $code }}" @selected(old('APP_LOCALE', $config['APP_LOCALE']) === $code)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>

                </div>
            </fieldset>
        </div>

        <div class="form-group">
            <h3 class="form-group-title">Base de données</h3>
            <fieldset class="form-element">
                <div class="form-field">
                    <label for="DB_CONNECTION" class="form-element-title">Type de base de données</label>
                    <select id="DB_CONNECTION" name="DB_CONNECTION" required>
                        @foreach($dbDrivers as $driver => $name)
                            <option value="{{ $driver }}" @selected(old('DB_CONNECTION', $config['DB_CONNECTION']) === $driver)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>

                </div>
            </fieldset>

            <div id="db-server-fields" class="{{ in_array(old('DB_CONNECTION', $config['DB_CONNECTION']), ['mysql', 'mariadb', 'pgsql']) ? '' : 'hidden' }}">
                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field" style="flex: 2;">
                            <label for="DB_HOST" class="form-element-title">Hôte</label>
                            <input
                                type="text"
                                id="DB_HOST"
                                name="DB_HOST"
                                value="{{ old('DB_HOST', $config['DB_HOST']) }}"
                                placeholder="127.0.0.1"
                            >

                        </div>

                        <div class="form-field" style="flex: 1;">
                            <label for="DB_PORT" class="form-element-title">Port</label>
                            <input
                                type="number"
                                id="DB_PORT"
                                name="DB_PORT"
                                value="{{ old('DB_PORT', $config['DB_PORT']) }}"
                                placeholder="3306"
                                min="1"
                                max="65535"
                            >

                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="DB_USERNAME" class="form-element-title">Utilisateur</label>
                            <input
                                type="text"
                                id="DB_USERNAME"
                                name="DB_USERNAME"
                                value="{{ old('DB_USERNAME', $config['DB_USERNAME']) }}"
                                placeholder="librerooms"
                            >

                        </div>

                        <div class="form-field">
                            <label for="DB_PASSWORD" class="form-element-title">Mot de passe</label>
                            <input
                                type="password"
                                id="DB_PASSWORD"
                                name="DB_PASSWORD"
                                value="{{ old('DB_PASSWORD') }}"
                                placeholder="{{ $config['DB_PASSWORD'] ? "**************" : "" }}"
                            >

                        </div>
                    </div>
                </fieldset>
            </div>

            <fieldset class="form-element">
                <div class="form-field">
                    <label for="DB_DATABASE" class="form-element-title">
                        <span id="db-database-label">Nom de la base de données</span>
                    </label>
                    <input
                        type="text"
                        id="DB_DATABASE"
                        name="DB_DATABASE"
                        value="{{ old('DB_DATABASE', $config['DB_DATABASE']) }}"
                        required
                        placeholder="librerooms"
                    >
                    <small id="db-database-hint" class="text-gray-600">
                        {{ in_array(old('DB_CONNECTION', $config['DB_CONNECTION']), ['mysql', 'mariadb', 'pgsql']) ? 'Le nom de la base de données existante' : 'Chemin vers le fichier SQLite (relatif à database/)' }}
                    </small>

                </div>
            </fieldset>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                Enregistrer et continuer
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dbConnection = document.getElementById('DB_CONNECTION');
    const serverFields = document.getElementById('db-server-fields');
    const dbDatabaseHint = document.getElementById('db-database-hint');
    const dbPort = document.getElementById('DB_PORT');

    function updateDbFields() {
        const driver = dbConnection.value;
        const isServer = ['mysql', 'mariadb', 'pgsql'].includes(driver);

        if (isServer) {
            serverFields.classList.remove('hidden');
            dbDatabaseHint.textContent = 'Le nom de la base de données existante';
            // Update default port based on driver
            if (driver === 'pgsql' && (!dbPort.value || dbPort.value === '3306')) {
                dbPort.value = '5432';
            } else if (['mysql', 'mariadb'].includes(driver) && (!dbPort.value || dbPort.value === '5432')) {
                dbPort.value = '3306';
            }
        } else {
            serverFields.classList.add('hidden');
            dbDatabaseHint.textContent = 'Chemin vers le fichier SQLite (relatif à database/)';
        }
    }

    dbConnection.addEventListener('change', updateDbFields);
    updateDbFields();
});
</script>
@endsection
