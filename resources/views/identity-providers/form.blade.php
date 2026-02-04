@extends('layouts.app')

@section('title', isset($provider) ? __('Edit identity provider') : __('New identity provider'))

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="form-header">
        <h1 class="form-title">
            {{ isset($provider) ? __('Edit identity provider') : __('New identity provider') }}
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
                <h3 class="form-group-title">{{ __('Basic information') }}</h3>

                <fieldset class="form-element">
                    <div class="form-element-row">
                        <div class="form-field">
                            <label for="name" class="form-element-title">{{ __('Name') }}</label>
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
                            <label for="driver" class="form-element-title">{{ __('Driver') }}</label>
                            <select name="driver" id="driver" required>
                                <option value="">{{ __('Select a driver') }}</option>
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
                            <span class="font-medium">{{ __('Enabled') }}</span>
                        </label>
                        <small class="text-gray-600">{{ __('Users will be able to log in via this provider') }}</small>
                    </div>
                </fieldset>
            </div>

            <!-- Configuration OIDC -->
            <div class="form-group">
                <h3 class="form-group-title">{{ __('OIDC configuration') }}</h3>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="issuer_url" class="form-element-title">{{ __('Issuer URL') }}</label>
                        <input
                            type="url"
                            id="issuer_url"
                            name="issuer_url"
                            value="{{ old('issuer_url', $provider?->issuer_url) }}"
                            required
                            placeholder="https://cloud.example.com"
                        >
                        <small class="text-gray-600">{{ __('Base URL of the identity provider') }}</small>
                        @error('issuer_url')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>

                <fieldset class="form-element">
                    <div class="form-field">
                        <label for="client_id" class="form-element-title">{{ __('Client ID') }}</label>
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
                            {{ __('Client Secret') }}
                            @if(isset($provider) && $provider->client_secret)
                                <span class="text-xs text-gray-500">({{ __('leave blank to keep unchanged') }})</span>
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

            <div class="btn-group justify-end mt-6">
                <a href="{{ route('identity-providers.index') }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($provider) ? __('Update') : __('Create') }}
                </button>
                @if(isset($provider))
                    <button type="button" onclick="confirmDeleteProvider()" class="btn btn-delete">
                        {{ __('Delete') }}
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
        if (confirm('{{ __('Are you sure you want to delete this identity provider? Linked users will no longer be able to log in via this provider.') }}')) {
            document.getElementById('delete-provider-form').submit();
        }
    }
</script>
@endif
@endsection
