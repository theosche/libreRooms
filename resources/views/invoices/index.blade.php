@extends('layouts.app')

@section('title', 'Factures')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">Factures</h1>

        @include('invoices._submenu', ['view' => $view, 'canViewAdmin' => $canViewAdmin])

        @if(!$canViewAdmin)
            <p class="mt-2 text-sm text-gray-600">Liste de toutes vos factures</p>
        @endif
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="view" value="{{ $view }}">

            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">Débiteur</label>
                <select name="tenant_id" id="tenant_id" class="form-select">
                    <option value="">Tous les débiteurs</option>
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
                    <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>À payer</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>En retard</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    Filtrer
                </button>
                @if(request()->hasAny(['tenant_id', 'status']))
                    <a href="{{ route('invoices.index', ['view' => $view]) }}" class="btn btn-secondary">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des factures -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Numéro
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Débiteur
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Montant
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        Date d'émission
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        Rappels
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        Échéance
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Statut
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                    @php
                        $computedStatus = $invoice->computed_status;
                    @endphp
                    <tr class="hover:bg-gray-50 details-on-mobile" onclick="toggleInvoiceDetails({{ $invoice->id }})">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a href="{{ route($invoice->reminder_count ? 'reservations.reminder.pdf' : 'reservations.invoice.pdf',
                                                $invoice->reservation->hash) }}"
                               target="_blank"
                               class="link-primary"
                               onclick="event.stopPropagation()">
                                {{ $invoice->number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="contact-info">
                                <span class="contact-info-name">{{ $invoice->reservation->tenant->display_name() }}</span>
                                <div class="contact-info-icons" onclick="event.stopPropagation()">
                                    @if($invoice->reservation->tenant->phone)
                                        <a href="tel:{{ $invoice->reservation->tenant->phone }}" class="text-blue-600 hover:text-blue-800" title="{{ $invoice->reservation->tenant->phone }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="mailto:{{ $invoice->reservation->tenant->email }}" class="text-blue-600 hover:text-blue-800" title="{{ $invoice->reservation->tenant->email }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ currency($invoice->amount, $invoice->owner) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 hide-on-mobile">
                            {{ $invoice->first_issued_at?->format('d.m.Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 hide-on-mobile">
                            {{ $invoice->reminder_count }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 hide-on-mobile">
                            {{ $invoice->due_at?->format('d.m.Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full whitespace-nowrap {{ $computedStatus->color() }}">
                                {{ $computedStatus->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="action-group" onclick="event.stopPropagation()">
                                @if($view === 'admin')
                                    {{-- Actions for admin view --}}
                                    @if(in_array($computedStatus, [\App\Enums\InvoiceStatus::LATE, \App\Enums\InvoiceStatus::TOO_LATE]))
                                        <form method="POST" action="{{ route('invoices.remind', $invoice) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="link-primary" onclick="return confirm('Envoyer un rappel de paiement ?')">
                                                Rappel
                                            </button>
                                        </form>
                                    @endif

                                    @if(!in_array($computedStatus, [\App\Enums\InvoiceStatus::PAID, \App\Enums\InvoiceStatus::CANCELLED]))
                                        <form method="POST" action="{{ route('invoices.pay', $invoice) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="link-success" onclick="return confirm('Marquer cette facture comme payée ?')">
                                                Payée
                                            </button>
                                        </form>

                                        <button type="button"
                                                onclick="openCancelModal({{ $invoice->id }})"
                                                class="link-danger">
                                            Annuler
                                        </button>
                                    @endif

                                    @if($invoice->canRecreate())
                                        <button type="button"
                                                onclick="openRecreateModal({{ $invoice->id }})"
                                                class="link-primary">
                                            Recréer
                                        </button>
                                    @endif
                                @else
                                    {{-- Actions for user view --}}
                                    @if ($invoice->reminder_count)
                                        <a href="{{ route('reservations.reminder.pdf', $invoice->reservation->hash) }}"
                                           target="_blank"
                                           class="link-primary">
                                            Voir PDF
                                        </a>
                                    @else
                                        <a href="{{ route('reservations.invoice.pdf', $invoice->reservation->hash) }}"
                                           target="_blank"
                                           class="link-primary">
                                            Voir PDF
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{-- Expandable details row --}}
                    <tr id="invoice-details-{{ $invoice->id }}" class="hidden bg-gray-50">
                        <td colspan="8" class="px-4 py-3">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Date d'émission:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->first_issued_at?->format('d.m.Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Échéance:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->due_at?->format('d.m.Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Rappels:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->reminder_count }}</span>
                                </div>
                                @if($invoice->paid_at)
                                    <div>
                                        <span class="text-gray-500">Payée le:</span>
                                        <span class="ml-1 text-gray-900">{{ $invoice->paid_at->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                                @if($invoice->cancelled_at)
                                    <div>
                                        <span class="text-gray-500">Annulée le:</span>
                                        <span class="ml-1 text-gray-900">{{ $invoice->cancelled_at->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                            Aucune facture trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $invoices->links() }}
    </div>
</div>

<script>
    function toggleInvoiceDetails(invoiceId) {
        const detailsRow = document.getElementById('invoice-details-' + invoiceId);
        const hideOnMobile = document.getElementsByClassName("hide-on-mobile");
        if (!detailsRow || hideOnMobile.length === 0) {
            return;
        }
        const isMobileView = getComputedStyle(hideOnMobile[0]).getPropertyValue('display') === 'none';
        if (isMobileView) {
            detailsRow.classList.toggle('hidden');
        }
    }
</script>

@if($view === 'admin')
    <!-- Modal d'annulation de facture -->
    <div id="cancel-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Annuler la facture</h3>
            <form id="cancel-form" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_email" value="1" checked
                               id="cancel-send-email"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Envoyer un email au débiteur</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="cancel-reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Raison de l'annulation (facultatif)
                    </label>
                    <textarea name="reason"
                              id="cancel-reason"
                              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="3"
                              placeholder="Expliquez la raison de l'annulation..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Cette raison sera incluse dans l'email si la case ci-dessus est cochée.</p>
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

    <!-- Modal de recréation de facture -->
    <div id="recreate-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Recréer la facture</h3>
            <form id="recreate-form" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_email" value="1" checked
                               id="recreate-send-email"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Envoyer un email au débiteur</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="recreate-reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Explication (facultatif)
                    </label>
                    <textarea name="reason"
                              id="recreate-reason"
                              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="3"
                              placeholder="Ajoutez une explication..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Cette explication sera incluse dans l'email si la case ci-dessus est cochée.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeRecreateModal()"
                            class="btn btn-secondary">
                        Retour
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Recréer la facture
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(invoiceId) {
            document.getElementById('cancel-form').action = '/invoices/' + invoiceId + '/cancel';
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

        function openRecreateModal(invoiceId) {
            document.getElementById('recreate-form').action = '/invoices/' + invoiceId + '/recreate';
            document.getElementById('recreate-modal').classList.remove('hidden');
            document.getElementById('recreate-reason').value = '';
            document.getElementById('recreate-send-email').checked = true;
            updateRecreateReasonState();
        }

        function closeRecreateModal() {
            document.getElementById('recreate-modal').classList.add('hidden');
        }

        function updateRecreateReasonState() {
            const checkbox = document.getElementById('recreate-send-email');
            const textarea = document.getElementById('recreate-reason');
            textarea.disabled = !checkbox.checked;
            textarea.classList.toggle('bg-gray-100', !checkbox.checked);
        }

        // Initialize event listeners
        document.getElementById('cancel-send-email').addEventListener('change', updateCancelReasonState);
        document.getElementById('recreate-send-email').addEventListener('change', updateRecreateReasonState);

        // Close modals on backdrop click
        document.getElementById('cancel-modal').addEventListener('click', function(e) {
            if (e.target === this) closeCancelModal();
        });
        document.getElementById('recreate-modal').addEventListener('click', function(e) {
            if (e.target === this) closeRecreateModal();
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCancelModal();
                closeRecreateModal();
            }
        });
    </script>
@endif
@endsection
