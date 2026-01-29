@extends('layouts.app')

@section('title', 'Mes réservations')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Réservations</h1>

        @if($canViewAdmin)
            <div class="mt-4 flex gap-2">
                <a href="{{ route('reservations.index', ['view' => 'mine']) }}"
                   class="px-4 py-2 rounded-md {{ $view === 'mine' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Mes réservations
                </a>
                <a href="{{ route('reservations.index', ['view' => 'admin']) }}"
                   class="px-4 py-2 rounded-md {{ $view === 'admin' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Réservations à gérer
                </a>
            </div>
        @else
            <p class="mt-2 text-sm text-gray-600">Liste de toutes vos réservations</p>
        @endif
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('reservations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="view" value="{{ $view }}">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">Salle</label>
                <select name="room_id" id="room_id" class="form-select">
                    <option value="">Toutes les salles</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                <select name="tenant_id" id="tenant_id" class="form-select">
                    <option value="">Tous les contacts</option>
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" {{ request('tenant_id') == $contact->id ? 'selected' : '' }}>
                            {{ $contact->display_name() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Enums\ReservationStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    Filtrer
                </button>
                @if(request()->hasAny(['room_id', 'tenant_id', 'status']))
                    <a href="{{ route('reservations.index', ['view' => $view]) }}" class="btn btn-secondary">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des réservations -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        N°
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Salle
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Locataire
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Titre
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Prix
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        État
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Créée le
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reservations as $reservation)
                    @php
                        $canManage = $user->canManageReservationsFor($reservation->room);
                        $isPending = $reservation->status === \App\Enums\ReservationStatus::PENDING;
                        $isConfirmed = $reservation->status === \App\Enums\ReservationStatus::CONFIRMED;
                        $isCancelled = $reservation->status === \App\Enums\ReservationStatus::CANCELLED;
                        $isFinished = $reservation->status === \App\Enums\ReservationStatus::FINISHED;
                        $canEdit = $reservation->isEditable();
                        $canCancel = $isPending || ($isConfirmed && $canManage);
                    @endphp
                    <tr class="hover:bg-gray-50 cursor-pointer transition" onclick="toggleDetails({{ $reservation->id }})">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $reservation->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $reservation->room->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center gap-2">
                                <span>{{ $reservation->tenant->display_name() }}</span>
                                @if($reservation->tenant->phone)
                                    <a href="tel:{{ $reservation->tenant->phone }}" class="text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </a>
                                @endif
                                <a href="mailto:{{ $reservation->tenant->email }}" class="text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $reservation->title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ currency($reservation->finalPrice(), $reservation->room->owner) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ match($reservation->status) {
                                \App\Enums\ReservationStatus::PENDING => 'bg-yellow-100 text-yellow-800',
                                \App\Enums\ReservationStatus::CONFIRMED => 'bg-green-100 text-green-800',
                                \App\Enums\ReservationStatus::FINISHED => 'bg-blue-100 text-blue-800',
                                \App\Enums\ReservationStatus::CANCELLED => 'bg-red-100 text-red-800',
                            } }}">
                                {{ $reservation->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reservation->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                            <div class="flex gap-3">
                                @if($canEdit)
                                    @if($isPending && $canManage)
                                        <a href="{{ route('reservations.edit', $reservation) }}" class="link-success">Contrôler</a>
                                    @else
                                        <a href="{{ route('reservations.edit', $reservation) }}" class="link-primary">Éditer</a>
                                    @endif
                                @endif

                                @if($canCancel)
                                    <button type="button"
                                            onclick="openCancelModal({{ $reservation->id }})"
                                            class="link-danger">
                                        Annuler
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Détails dépliables -->
                    <tr id="details-{{ $reservation->id }}" class="details-row hidden">
                        <td colspan="8" class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Colonne 1: Description & Événements -->
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h4>
                                        <p class="text-sm text-slate-700">{{ $reservation->description ?: '—' }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Événements</h4>
                                        <div class="space-y-1">
                                            @foreach($reservation->events as $event)
                                                <div class="text-sm text-slate-700 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>{{ $event->startLocalTz()->format('d.m.Y H:i') }} → {{ $event->endLocalTz()->format('H:i') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne 2: Champs personnalisés & Documents -->
                                <div class="space-y-4">
                                    @if($reservation->customFieldValues->count() > 0)
                                        <div>
                                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Informations complémentaires</h4>
                                            <dl class="space-y-1">
                                                @foreach($reservation->customFieldValues as $fieldValue)
                                                    <div class="text-sm flex gap-2">
                                                        <dt class="font-medium text-slate-600 shrink-0">{{ $fieldValue->label }}:</dt>
                                                        <dd class="text-slate-700">
                                                            {{ implode(', ', $fieldValue->labelValue()) }}
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    @endif

                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Documents</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @if(!$isCancelled)
                                                <a href="{{ route('reservations.prebook.pdf', $reservation->hash) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Pré-réservation
                                                </a>
                                            @endif
                                            @if($reservation->invoice)
                                                <a href="{{ route('reservations.invoice.pdf', $reservation->hash) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Facture {{ $reservation->invoice->number }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne 3: Facture -->
                                @if($reservation->invoice)
                                    @php $invoiceStatus = $reservation->invoice->computed_status; @endphp
                                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Facture</h4>
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $invoiceStatus->color() }}">
                                                {{ $invoiceStatus->label() }}
                                            </span>
                                        </div>
                                        <dl class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <dt class="text-slate-500">Émise le</dt>
                                                <dd class="text-slate-900 font-medium">{{ $reservation->invoice->issued_at?->format('d.m.Y') ?? '—' }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-slate-500">Échéance</dt>
                                                <dd class="text-slate-900 font-medium">{{ $reservation->invoice->due_at?->format('d.m.Y') ?? '—' }}</dd>
                                            </div>
                                            @if($reservation->invoice->reminder_count > 0)
                                                <div class="flex justify-between">
                                                    <dt class="text-slate-500">Rappels</dt>
                                                    <dd class="text-orange-600 font-medium">{{ $reservation->invoice->reminder_count }}</dd>
                                                </div>
                                            @endif
                                            @if($reservation->invoice->paid_at)
                                                <div class="flex justify-between pt-2 border-t border-slate-100">
                                                    <dt class="text-green-600">Payée le</dt>
                                                    <dd class="text-green-600 font-medium">{{ $reservation->invoice->paid_at->format('d.m.Y') }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            Aucune réservation trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $reservations->links() }}
    </div>
</div>

<!-- Modal de chargement -->
<div id="loader-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 shadow-xl flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
        <p class="text-gray-700 font-medium">Traitement en cours...</p>
    </div>
</div>

<!-- Modal d'annulation -->
<div id="cancel-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Annuler la réservation</h3>
        <form id="cancel-form" method="POST">
            @csrf
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" checked
                           id="cancel-send-email"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Envoyer un email d'annulation</span>
                </label>
            </div>
            <div class="mb-4">
                <label for="cancel-reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Raison de l'annulation (facultatif)
                </label>
                <textarea name="cancellation_reason"
                          id="cancel-reason"
                          class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                          rows="3"
                          placeholder="Expliquez la raison de l'annulation..."></textarea>
                <p class="mt-1 text-xs text-gray-500">Cette raison sera incluse dans l'email d'annulation si la case ci-dessus est cochée.</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="closeCancelModal()"
                        class="btn btn-secondary">
                    Retour
                </button>
                <button type="submit" class="btn btn-delete">
                    Confirmer l'annulation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDetails(reservationId) {
        const detailsRow = document.getElementById(`details-${reservationId}`);
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

    function openCancelModal(reservationId) {
        document.getElementById('cancel-form').action = '/reservations/' + reservationId + '/cancel';
        document.getElementById('cancel-modal').classList.remove('hidden');
        document.getElementById('cancel-reason').value = '';
        document.getElementById('cancel-send-email').checked = true;
        updateCancelReasonState();
    }

    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
    }

    function updateCancelReasonState() {
        const checkbox = document.getElementById('cancel-send-email');
        const textarea = document.getElementById('cancel-reason');
        textarea.disabled = !checkbox.checked;
        textarea.classList.toggle('bg-gray-100', !checkbox.checked);
    }

    // Initialize event listener for checkbox
    document.getElementById('cancel-send-email').addEventListener('change', updateCancelReasonState);

    // Show loader when cancel form is submitted
    document.getElementById('cancel-form').addEventListener('submit', function() {
        closeCancelModal();
        document.getElementById('loader-modal').classList.remove('hidden');
    });

    // Close modal on backdrop click
    document.getElementById('cancel-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancelModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCancelModal();
        }
    });
</script>
@endsection
