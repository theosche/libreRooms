@extends('layouts.app')

@section('title', 'Utilisateurs - ' . $room->name)

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Utilisateurs</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Gérer les utilisateurs ayant accès à la salle <strong>{{ $room->name }}</strong>
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Propriétaire : {{ $room->owner->contact->display_name() }}
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-secondary">
                    Modifier la salle
                </a>
                <button type="button" onclick="openAddUserModal()" class="btn btn-primary">
                    Ajouter un utilisateur
                </button>
            </div>
        </div>
    </div>

    @if(!$room->is_public)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Salle privée</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Cette salle est privée. Seuls les utilisateurs listés ci-dessous et les utilisateurs ayant un rôle sur le propriétaire peuvent la voir et la réserver.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Salle publique</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        Cette salle est publique et accessible à tous. Les utilisateurs ci-dessous ont un accès explicite (utile si la salle devient privée).
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rôle
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ajouté le
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($room->users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ \App\Enums\RoomUserRoles::tryFrom($user->pivot->role)?->label() ?? $user->pivot->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->pivot->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="{{ route('rooms.users.destroy', [$room, $user]) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cet utilisateur ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-danger">
                                    Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Aucun utilisateur n'a d'accès direct à cette salle.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <a href="{{ route('rooms.index', ['view' => 'mine']) }}" class="btn btn-secondary">
            Retour aux salles
        </a>
    </div>
</div>

<!-- Modal Ajouter un utilisateur -->
<div id="add-user-modal" class="fixed inset-0 bg-gray-600/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter un utilisateur</h3>
        <p class="text-sm text-gray-600 mb-4">
            Ajouter un utilisateur à la salle "<strong>{{ $room->name }}</strong>"
        </p>
        <form action="{{ route('rooms.users.store', $room) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email de l'utilisateur
                </label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('email') }}"
                       placeholder="utilisateur@exemple.com">
                <p class="text-xs text-gray-500 mt-1">L'utilisateur doit avoir un compte existant</p>
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    Rôle
                </label>
                <select name="role" id="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeAddUserModal()" class="btn btn-secondary">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() {
        document.getElementById('add-user-modal').classList.remove('hidden');
        document.getElementById('add-user-modal').classList.add('flex');
        document.getElementById('email').focus();
    }

    function closeAddUserModal() {
        document.getElementById('add-user-modal').classList.add('hidden');
        document.getElementById('add-user-modal').classList.remove('flex');
    }

    // Fermer en cliquant sur le fond
    document.getElementById('add-user-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddUserModal();
        }
    });

    // Ouvrir le modal si erreur de validation
    @if($errors->any())
        openAddUserModal();
    @endif

    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddUserModal();
        }
    });
</script>
@endsection
