@extends('layouts.app')

@section('title', __('Users') . ' - ' . $owner->contact->display_name())

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Users') }}</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Manage users with access to owner') }} <strong>{{ $owner->contact->display_name() }}</strong>
        </p>
        <nav class="page-submenu">
            <a href="{{ redirect_back_url('owners.index') }}"
               class="page-submenu-item page-submenu-nav">
                {{ __('Back to owners') }}
            </a>
            <span class="page-submenu-separator"></span>
            <button type="button" onclick="openAddUserModal()" class="page-submenu-item page-submenu-action cursor-pointer">
                + {{ __('Add user') }}
            </button>
        </nav>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">{{ __('Roles and permissions') }}</h3>
                <p class="mt-1 text-sm text-blue-700">
                    <strong>{{ __('Administrator') }}</strong> : {{ __('full management of owner and its rooms') }}<br>
                    <strong>{{ __('Moderator') }}</strong> : {{ __('management of reservations and viewer access') }}<br>
                    <strong>{{ __('Viewer') }}</strong> : {{ __('read-only access to private rooms') }}
                </p>
            </div>
        </div>
    </div>

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
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($owner->users as $user)
                    @php
                        $userRole = \App\Enums\UserRole::tryFrom($user->pivot->role);
                        $canRemove = auth()->user()->can('removeOwnerUser', [$owner, $user]);
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            @if($currentUser->canAccessUser($user))
                                <a href="{{ route('users.edit', $user) }}" onclick="event.stopPropagation()">
                                    {{ $user->name }}
                                </a>
                            @else
                                {{ $user->name }}
                            @endif
                            @if($user->id === auth()->id())
                                <span class="text-xs text-gray-500">({{ __('you') }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badgeColor = match($userRole) {
                                    \App\Enums\UserRole::ADMIN => 'bg-red-100 text-red-800',
                                    \App\Enums\UserRole::MODERATOR => 'bg-yellow-100 text-yellow-800',
                                    \App\Enums\UserRole::VIEWER => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                                {{ $userRole?->label_short() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="action-group">
                            @if($canRemove)
                                <form action="{{ route('owners.users.destroy', [$owner, $user] + redirect_back_query()) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('Are you sure you want to remove this user?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">
                                        {{ __('Remove') }}
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No user has access to this owner.') }}
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
            {{ __('Add a user to owner') }} "<strong>{{ $owner->contact->display_name() }}</strong>"
        </p>
        <form action="{{ route('owners.users.store', [$owner] + redirect_back_query()) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('User email') }}
                </label>
                <input type="email" name="email" id="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('email') }}"
                       placeholder="{{ __('user@example.com') }}">
                <p class="text-xs text-gray-500 mt-1">{{ __('User must have an existing account. If the user already exists, their role will be updated.') }}</p>
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
                            $canAddRole = auth()->user()->can('addOwnerUser', [$owner, $role->value]);
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
