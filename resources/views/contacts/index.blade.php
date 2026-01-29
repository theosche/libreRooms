@extends('layouts.app')

@section('title', 'Mes contacts')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
            <p class="mt-2 text-sm text-gray-600">Liste de tous vos contacts</p>
        </div>
        <a href="{{ route('contacts.create') }}" class="btn btn-primary">
            Nouveau contact
        </a>
    </div>

    <!-- Tableau des contacts -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Entité
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Prénom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Adresse
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Partagé avec
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($contact->type->value === 'individual')
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Privé·e">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Organisation">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $contact->entity_name ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $contact->first_name ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $contact->last_name ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center gap-2">
                                @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}" class="text-blue-600 hover:text-blue-800" title="Email: {{ $contact->email }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if($contact->invoice_email)
                                    <a href="mailto:{{ $contact->invoice_email }}" class="text-green-600 hover:text-green-800" title="Email facturation: {{ $contact->invoice_email }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if($contact->phone)
                                    <a href="tel:{{ $contact->phone }}" class="text-blue-600 hover:text-blue-800" title="Téléphone: {{ $contact->phone }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if(!$contact->email && !$contact->invoice_email && !$contact->phone)
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @if($contact->street || $contact->zip || $contact->city)
                                <div>
                                    @if($contact->street)
                                        <div>{{ $contact->street }}</div>
                                    @endif
                                    @if($contact->zip || $contact->city)
                                        <div>{{ $contact->zip }} {{ $contact->city }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @php
                                $otherUsers = $contact->users->where('id', '!=', $user->id);
                            @endphp
                            @if($otherUsers->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($otherUsers as $sharedUser)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                            {{ $sharedUser->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">Vous seul·e</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex gap-3">
                                <a href="#" class="link-primary" onclick="event.preventDefault(); showShareModal({{ $contact->id }}, '{{ addslashes($contact->display_name()) }}')">
                                    Partager
                                </a>
                                <a href="{{ route('contacts.edit', $contact) }}" class="link-primary">
                                    Modifier
                                </a>
                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    @if($otherUsers->count() > 0)
                                        <button type="submit" class="link-danger" onclick="return confirm('Êtes-vous sûr de vouloir retirer ce contact de votre liste ? D\'autres utilisateurs y ont également accès, il ne sera pas supprimé définitivement.')">
                                            Retirer
                                        </button>
                                    @else
                                        <button type="submit" class="link-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce contact ? Cette action est irréversible.')">
                                            Supprimer
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            Aucun contact trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $contacts->links() }}
    </div>
</div>

<!-- Modal de partage -->
<div id="shareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Partager le contact</h3>
        <p class="text-sm text-gray-600 mb-4">
            Partager "<span id="shareContactName"></span>" avec un autre utilisateur
        </p>
        <form method="POST" action="#" id="shareForm">
            @csrf
            <div class="mb-4">
                <label for="share_email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email de l'utilisateur
                </label>
                <input type="email" name="email" id="share_email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="email@exemple.com">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="hideShareModal()" class="btn btn-secondary">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    Partager
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showShareModal(contactId, contactName) {
        document.getElementById('shareContactName').textContent = contactName;
        document.getElementById('shareForm').action = `/contacts/${contactId}/share`;
        document.getElementById('shareModal').classList.remove('hidden');
        document.getElementById('shareModal').classList.add('flex');
    }

    function hideShareModal() {
        document.getElementById('shareModal').classList.add('hidden');
        document.getElementById('shareModal').classList.remove('flex');
        document.getElementById('share_email').value = '';
    }

    // Close modal on click outside
    document.getElementById('shareModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideShareModal();
        }
    });
</script>
@endsection