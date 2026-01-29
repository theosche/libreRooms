@extends('layouts.app')

@section('title', 'Fournisseurs d\'identité')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Réglages système</h1>
                <p class="mt-2 text-sm text-gray-600">Gestion des fournisseurs d'identité (OIDC)</p>
            </div>
            <a href="{{ route('identity-providers.create') }}" class="btn btn-primary">
                Nouveau fournisseur d'identité
            </a>
        </div>

        @include('system-settings._submenu')
    </div>

    <!-- Tableau des fournisseurs d'identité -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Slug
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Driver
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Statut
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($providers as $provider)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $provider->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 font-mono">
                                {{ $provider->slug }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ ucfirst($provider->driver) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($provider->enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('identity-providers.edit', $provider) }}" class="link-primary">
                                    Modifier
                                </a>
                                <button type="button" onclick="confirmDelete({{ $provider->id }})" class="link-danger">
                                    Supprimer
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
                            Aucun fournisseur d'identité configuré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(providerId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur d\'identité ? Les utilisateurs liés ne pourront plus se connecter via ce fournisseur.')) {
            document.getElementById('delete-form-' + providerId).submit();
        }
    }
</script>
@endsection
