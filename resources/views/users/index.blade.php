@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion des utilisateurs</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $users->total() }} utilisateur(s) au total
                </p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                Créer un utilisateur
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filtre par propriétaire -->
                <div>
                    <label for="owner_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Droits admin
                    </label>
                    <select name="owner_id" id="owner_id" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Tout afficher</option>
                        <option value="admin" @selected(request('owner_id') == "admin")>Tous les comptes admins</option>
                        <option value="not_admin" @selected(request('owner_id') == "not_admin")>Tous les comptes sans droits admins</option>
                        <option value="global_admin" @selected(request('owner_id') == "global_admin")>Administrateurs globaux</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" @selected(request('owner_id') == $owner->id)>
                                Admin pour: {{ $owner->contact->display_name() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['owner_id', 'global_admin_only']))
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Utilisateur
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contacts
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rôles
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </div>
                                    @if(!$user->email_verified_at)
                                        <div class="text-xs text-yellow-600">Email non vérifié</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $user->contacts->count() }} contact(s)
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                @if($user->is_global_admin)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Admin global
                                    </span>
                                @endif

                                @if($user->owners->isNotEmpty())
                                    <div class="mt-1 space-y-1">
                                        @foreach($user->owners as $owner)
                                            @php
                                                $ownerRole = \App\Enums\OwnerUserRoles::tryFrom($owner->pivot->role);
                                                $badgeColor = match($ownerRole) {
                                                    \App\Enums\OwnerUserRoles::ADMIN => 'bg-red-100 text-red-800',
                                                    \App\Enums\OwnerUserRoles::MODERATOR => 'bg-yellow-100 text-yellow-800',
                                                    \App\Enums\OwnerUserRoles::VIEWER => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <div class="text-xs">
                                                <span class="font-medium">{{ $owner->contact->display_name() }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColor }}">
                                                    {{ $ownerRole?->label() ?? $owner->pivot->role }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!$user->is_global_admin && $user->owners->isEmpty())
                                    <span class="text-xs text-gray-500 italic">Aucun rôle</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="link-primary">
                                    Modifier
                                </a>
                                @if($user->id !== auth()->id())
                                    <button type="button" onclick="confirmDelete({{ $user->id }})" class="link-danger">
                                        Supprimer
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal de confirmation de suppression -->
<script>
    function confirmDelete(userId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
            document.getElementById('delete-form-' + userId).submit();
        }
    }
</script>
@endsection
