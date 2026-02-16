@extends('layouts.app')

@section('title', __('Identity providers'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('System settings') }}</h1>
        @include('system-settings._submenu')
        <p class="mt-2 text-sm text-gray-600">{{ __('Manage identity providers (OIDC)') }}</p>
    </div>

    <!-- Tableau des fournisseurs d'identité -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Name') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Slug') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Driver') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($providers as $provider)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $provider->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-500 font-mono">
                                {{ $provider->slug }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ ucfirst($provider->driver) }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($provider->enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="action-group">
                                <a href="{{ route('identity-providers.edit', $provider) }}" class="link-primary" title="{{ __('Edit') }}">
                                    <x-action-icon action="edit" />
                                </a>
                                <button type="button" onclick="confirmDelete({{ $provider->id }})" class="link-danger" title="{{ __('Delete') }}">
                                    <x-action-icon action="delete" />
                                </button>
                                <form id="delete-form-{{ $provider->id }}" action="{{ route('identity-providers.destroy', $provider) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ __('No identity provider configured.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(providerId) {
        if (confirm('{{ __('Are you sure you want to delete this identity provider? Linked users will no longer be able to log in via this provider.') }}')) {
            document.getElementById('delete-form-' + providerId).submit();
        }
    }
</script>
@endsection
