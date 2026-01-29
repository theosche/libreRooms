@extends('layouts.app')

@section('title', 'Salles disponibles')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Salles</h1>

                @include('rooms._submenu', ['view' => $view])

                @cannot('viewMine', App\Models\Room::class)
                    <p class="mt-2 text-sm text-gray-600">Liste de toutes les salles disponibles à la réservation</p>
                @endcannot
            </div>

            @can('create', App\Models\Room::class)
                <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                    Nouvelle salle
                </a>
            @endcan
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('rooms.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="view" value="{{ $view }}">
            <div>
                <label for="owner_id" class="block text-sm font-medium text-gray-700 mb-1">Propriétaire</label>
                <select name="owner_id" id="owner_id" class="form-select">
                    <option value="">Tous les propriétaires</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->contact->display_name() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 col-span-2">
                <button type="submit" class="btn btn-primary">
                    Filtrer
                </button>
                @if(request()->has('owner_id'))
                    <a href="{{ route('rooms.index', ['view' => $view]) }}" class="btn btn-secondary">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des salles -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Propriétaire
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    @if($view === 'mine')
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50 cursor-pointer transition" onclick="toggleDetails({{ $room->id }})">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $room->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $room->owner->contact->display_name() }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ Str::limit($room->description, 100) }}
                        </td>
                        @if($view === 'mine')
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-1 flex-wrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $room->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $room->active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if(!$room->is_public)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Privée
                                        </span>
                                    @endif
                                </div>
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                            <div class="flex gap-3">
                                <a href="{{ route('reservations.create', $room) }}" class="link-primary">
                                    Réserver
                                </a>

                                @can('manageUsers', $room)
                                    <a href="{{ route('rooms.users.index', $room) }}" class="link-primary">
                                        Utilisateurs
                                    </a>
                                @endcan

                                @can('update', $room)
                                    <a href="{{ route('rooms.edit', $room) }}" class="link-primary">
                                        Modifier
                                    </a>

                                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    <!-- Détails dépliables -->
                    <tr id="details-{{ $room->id }}" class="details-row hidden">
                        <td colspan="{{ $view === 'mine' ? 5 : 4 }}" class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <!-- Charte -->
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Charte</h4>
                                    @if($room->charter_mode->value === 'text')
                                        <p class="text-sm text-slate-700 line-clamp-4">{{ $room->charter_str }}</p>
                                    @elseif($room->charter_mode->value === 'link')
                                        <a href="{{ $room->charter_str }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Voir la charte
                                        </a>
                                    @else
                                        <p class="text-sm text-slate-400">Aucune charte</p>
                                    @endif
                                </div>

                                <!-- Tarifs -->
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Tarifs</h4>
                                    @if($room->price_mode->value === 'fixed')
                                        <div class="bg-white rounded-lg border border-slate-200 p-3 space-y-2">
                                            @if($room->price_short && $room->max_hours_short)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-slate-500">{{ $room->shortPriceRuleLabel() }}</span>
                                                    <span class="text-slate-900 font-medium">{{ currency($room->price_short, $room->owner) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between text-sm">
                                                <span class="text-slate-500">Journée</span>
                                                <span class="text-slate-900 font-medium">{{ currency($room->price_full_day, $room->owner) }}</span>
                                            </div>
                                        </div>
                                    @elseif($room->price_mode->value === 'free')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">Libre participation</span>
                                    @else
                                        <p class="text-sm text-slate-400">Non spécifié</p>
                                    @endif

                                    @if($room->discounts->where('active', true)->count() > 0)
                                        <div class="mt-3 space-y-1">
                                            @foreach($room->discounts->where('active', true) as $discount)
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="text-green-600 font-medium">
                                                        @if($discount->type->value === 'fixed')
                                                            {{ currency(-$discount->value, $room->owner) }}
                                                        @else
                                                            -{{ $discount->value }}%
                                                        @endif
                                                    </span>
                                                    <span class="text-slate-600">{{ $discount->name }}</span>
                                                    @if($discount->limit_to_contact_type)
                                                        <span class="text-slate-400 text-xs">({{ $discount->limit_to_contact_type->value === 'individual' ? 'Privé' : 'Org.' }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Règles -->
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Règles</h4>
                                    <dl class="space-y-2 text-sm">
                                        @if($room->reservation_cutoff_days)
                                            <div class="flex">
                                                <dt class="text-slate-500 mr-4">Délai min.</dt>
                                                <dd class="text-slate-900">{{ $room->reservation_cutoff_days }}j avant</dd>
                                            </div>
                                        @endif
                                        @if($room->reservation_advance_limit)
                                            <div class="flex">
                                                <dt class="text-slate-500 mr-4">Rés. max.</dt>
                                                <dd class="text-slate-900">{{ $room->reservation_advance_limit }}j à l'avance</dd>
                                            </div>
                                        @endif
                                        @if(!$room->reservation_cutoff_days && !$room->reservation_advance_limit)
                                            <p class="text-slate-400">Aucune restriction</p>
                                        @endif
                                    </dl>
                                </div>

                                <!-- Options -->
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Options</h4>
                                    @if($room->options->where('active', true)->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($room->options->where('active', true) as $option)
                                                <div class="bg-white rounded-lg border border-slate-200 p-2">
                                                    <div class="flex justify-between items-start">
                                                        <span class="text-sm text-slate-700">{{ $option->name }}</span>
                                                        <span class="text-sm text-slate-900 font-medium shrink-0 ml-2">{{ currency($option->price, $room->owner) }}</span>
                                                    </div>
                                                    @if($option->description)
                                                        <p class="text-xs text-slate-500 mt-1">{{ $option->description }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-400">Aucune option</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $view === 'mine' ? 5 : 4 }}" class="px-6 py-4 text-center text-gray-500">
                            Aucune salle trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $rooms->links() }}
    </div>
</div>

<script>
    function toggleDetails(roomId) {
        const detailsRow = document.getElementById(`details-${roomId}`);
        if (detailsRow.classList.contains('hidden')) {
            // Fermer tous les autres détails
            document.querySelectorAll('[id^="details-"]').forEach(row => {
                row.classList.add('hidden');
            });
            // Ouvrir celui-ci
            detailsRow.classList.remove('hidden');
        } else {
            detailsRow.classList.add('hidden');
        }
    }
</script>
@endsection
