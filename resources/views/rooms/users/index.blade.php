@extends('layouts.app')

@section('title', __('Users') . ' - ' . $room->name)

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Users') }}</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Manage users with access to room') }} <strong>{{ $room->name }}</strong>
        </p>
        <p class="mt-1 text-xs text-gray-500">
            {{ __('Owner') }}: {{ $room->owner->contact->display_name() }}
        </p>
         <nav class="page-submenu">
             <a href="{{ redirect_back_url('rooms.index', ['view' => 'mine']) }}"
                class="page-submenu-item page-submenu-nav">
                 {{ __('Back to rooms') }}
             </a>
             <span class="page-submenu-separator"></span>
             <button type="button" onclick="openAddUserModal()" class="page-submenu-item page-submenu-action cursor-pointer">
                + {{ __('Add user') }}
             </button>
        </nav>
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
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('Private room') }}</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        {{ __('This room is private. Only the users listed below and users with a role on the owner can view and book it.') }}
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
                    <h3 class="text-sm font-medium text-blue-800">{{ __('Public room') }}</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        {{ __('This room is public and accessible to everyone. The users below have explicit access (useful if the room becomes private).') }}
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
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Name') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Email') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Role') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Added on') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($room->users as $user)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            @if($currentUser->canAccessUser($user))
                                <a href="{{ route('users.edit', $user) }}" onclick="event.stopPropagation()">
                                    {{ $user->name }}
                                </a>
                            @else
                                {{ $user->name }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ \App\Enums\UserRole::tryFrom($user->pivot->role)?->label_short() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 hide-on-mobile">
                            {{ $user->pivot->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <div class="action-group">
                                @can('removeRoomUser', [$room, $user])
                                    <form action="{{ route('rooms.users.destroy', [$room, $user] + redirect_back_query()) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ __('Are you sure you want to remove this user?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-danger">
                                            {{ __('Remove') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No user has direct access to this room.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter un utilisateur -->
<div id="add-user-modal" class="fixed inset-0 bg-gray-600/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Add user') }}</h3>
        <p class="text-sm text-gray-600 mb-4">
            {{ __('Add a user to room') }} "<strong>{{ $room->name }}</strong>"
        </p>
        <form action="{{ route('rooms.users.store', [$room] + redirect_back_query()) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('User email') }}
                </label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('email') }}"
                       placeholder="{{ __('user@example.com') }}">
                <p class="text-xs text-gray-500 mt-1">{{ __('User must have an existing account') }}</p>
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Role') }}
                </label>
                <select name="role" id="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($roles as $role)
                        @php
                            $canAddRole = auth()->user()->can('addRoomUser', [$room, $role->value]);
                        @endphp
                        @if($canAddRole)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                                {{ $role->label() }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('role')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeAddUserModal()" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('Add') }}
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
