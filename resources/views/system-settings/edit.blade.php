@extends('layouts.app')

@section('title', 'Réglages système')

@section('page-script')
    @vite(['resources/js/system-settings/system-settings-form.js'])
    <script>
        window.systemSettingsId = {{ $settings?->id ?? 'null' }};
    </script>
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Réglages système</h1>
        <p class="mt-2 text-sm text-gray-600">Configuration globale de l'application</p>

        @include('system-settings._submenu')
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('system-settings.update') }}" class="styled-form">
            @csrf
            @method('PUT')

            <!-- Configuration email -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration email</h3>
                <p class="text-sm text-gray-600 mb-4">Configuration SMTP pour l'envoi des emails (requis)</p>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="mail_host" class="form-element-title">Serveur SMTP</label>
                            <input
                                type="text"
                                id="mail_host"
                                name="mail_host"
                                value="{{ old('mail_host', $settings?->mail_host) }}"
                                required
                            >
                            @error('mail_host')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="mail_port" class="form-element-title">Port</label>
                            <input
                                type="number"
                                id="mail_port"
                                name="mail_port"
                                value="{{ old('mail_port', $settings?->mail_port) }}"
                                required
                                min="1"
                                max="65535"
                            >
                            @error('mail_port')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="mail" class="form-element-title">Email (utilisateur SMTP)</label>
                            <input
                                type="text"
                                id="mail"
                                name="mail"
                                value="{{ old('mail', $settings?->mail) }}"
                                required
                            >
                            @error('mail')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="mail_pass" class="form-element-title">
                                Mot de passe SMTP
                                @if($settings?->mail_pass)
                                    <span class="text-xs text-gray-500">(laisser vide pour conserver)</span>
                                @endif
                            </label>
                            <input
                                type="password"
                                id="mail_pass"
                                name="mail_pass"
                                value="{{ old('mail_pass') }}"
                                {{ $settings?->mail_pass ? '' : 'required' }}
                                @if($settings?->mail_pass)
                                    placeholder="***************"
                                @endif
                            >
                            @error('mail_pass')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <div id="mail-status" class="config-status hidden"></div>
            </div>

            <!-- Configuration CalDAV -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration CalDAV par défaut</h3>
                <p class="text-sm text-gray-600 mb-4">Configuration CalDAV utilisable par les propriétaires (facultatif)</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="dav_url" class="form-element-title">URL CalDAV</label>
                        <input
                            type="text"
                            id="dav_url"
                            name="dav_url"
                            value="{{ old('dav_url', $settings?->dav_url) }}"
                        >
                        @error('dav_url')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="dav_user" class="form-element-title">Utilisateur CalDAV</label>
                            <input
                                type="text"
                                id="dav_user"
                                name="dav_user"
                                value="{{ old('dav_user', $settings?->dav_user) }}"
                            >
                            @error('dav_user')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="dav_pass" class="form-element-title">
                                Mot de passe CalDAV
                                @if($settings?->dav_pass)
                                    <span class="text-xs text-gray-500">(laisser vide pour conserver)</span>
                                @endif
                            </label>
                            <input
                                type="password"
                                id="dav_pass"
                                name="dav_pass"
                                value="{{ old('dav_pass') }}"
                                @if($settings?->dav_pass)
                                    placeholder="***************"
                                @endif
                            >
                            @error('dav_pass')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <div id="caldav-status" class="config-status hidden"></div>
            </div>

            <!-- Configuration WebDAV -->
            <div class="form-group">
                <h3 class="form-group-title">Configuration WebDAV par défaut</h3>
                <p class="text-sm text-gray-600 mb-4">Configuration WebDAV utilisable par les propriétaires (facultatif)</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="webdav_endpoint" class="form-element-title">Endpoint WebDAV</label>
                        <input
                            type="text"
                            id="webdav_endpoint"
                            name="webdav_endpoint"
                            value="{{ old('webdav_endpoint', $settings?->webdav_endpoint) }}"
                        >
                        @error('webdav_endpoint')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="webdav_user" class="form-element-title">Utilisateur WebDAV</label>
                            <input
                                type="text"
                                id="webdav_user"
                                name="webdav_user"
                                value="{{ old('webdav_user', $settings?->webdav_user) }}"
                            >
                            @error('webdav_user')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="webdav_pass" class="form-element-title">
                                Mot de passe WebDAV
                                @if($settings?->webdav_pass)
                                    <span class="text-xs text-gray-500">(laisser vide pour conserver)</span>
                                @endif
                            </label>
                            <input
                                type="password"
                                id="webdav_pass"
                                name="webdav_pass"
                                value="{{ old('webdav_pass') }}"
                                @if($settings?->webdav_pass)
                                    placeholder="***************"
                                @endif
                            >
                            @error('webdav_pass')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="webdav_save_path" class="form-element-title">Chemin de sauvegarde</label>
                        <input
                            type="text"
                            id="webdav_save_path"
                            name="webdav_save_path"
                            value="{{ old('webdav_save_path', $settings?->webdav_save_path) }}"
                        >
                        @error('webdav_save_path')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <div id="webdav-status" class="config-status hidden"></div>
            </div>

            <!-- Paramètres régionaux -->
            <div class="form-group">
                <h3 class="form-group-title">Paramètres régionaux</h3>
                <p class="text-sm text-gray-600 mb-4">Paramètres par défaut pour l'application (requis)</p>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="timezone" class="form-element-title">Fuseau horaire</label>
                        @include('partials._timezone_select', [
                            'name' => 'timezone',
                            'id' => 'timezone',
                            'value' => old('timezone', $settings?->timezone),
                            'showDefaultOption' => false,
                            'required' => true,
                        ])
                        @error('timezone')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="currency" class="form-element-title">Devise</label>
                            @include('partials._currency_select', [
                                'name' => 'currency',
                                'id' => 'currency',
                                'value' => old('currency', $settings?->currency),
                                'showDefaultOption' => false,
                                'required' => true,
                            ])
                            @error('currency')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="locale" class="form-element-title">Langue</label>
                            @include('partials._locale_select', [
                                'name' => 'locale',
                                'id' => 'locale',
                                'value' => old('locale', $settings?->locale),
                                'showDefaultOption' => false,
                                'required' => true,
                            ])
                            @error('locale')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <button type="submit" class="btn btn-primary">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
