@extends('layouts.app')

@section('title', __('Invoices'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Invoices') }}</h1>

        @include('invoices._submenu', ['view' => $view, 'canViewAdmin' => $canViewAdmin])

        @if(!$canViewAdmin)
            <p class="mt-2 text-sm text-gray-600">{{ __('List of all your invoices') }}</p>
        @endif
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="view" value="{{ $view }}">

            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Debtor') }}</label>
                <select name="tenant_id" id="tenant_id" class="form-select">
                    <option value="">{{ __('All debtors') }}</option>
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" {{ request('tenant_id') == $contact->id ? 'selected' : '' }}>
                            {{ $contact->display_name() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>{{ __('Due') }}</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>{{ __('Late') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    {{ __('Filter') }}
                </button>
                @if(request()->hasAny(['tenant_id', 'status']))
                    <a href="{{ route('invoices.index', ['view' => $view]) }}" class="btn btn-secondary">
                        {{ __('Reset') }}
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
                        {{ __('Number') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Debtor') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Amount') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Issue date') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Reminders') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Due date') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
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
                                @if($user->canAccessContact($invoice->reservation->tenant))
                                    <a href="{{ route('contacts.edit', $invoice->reservation->tenant) }}" onclick="event.stopPropagation()">
                                        <span class="contact-info-name">{{ $invoice->reservation->tenant->display_name() }}</span>
                                    </a>
                                @else
                                    <span class="contact-info-name">{{ $invoice->reservation->tenant->display_name() }}</span>
                                @endif
                                <div class="contact-info-icons">
                                    @if($invoice->reservation->tenant->phone)
                                        <a href="tel:{{ $invoice->reservation->tenant->phone }}" onclick="event.stopPropagation()"
                                           class="text-blue-600 hover:text-blue-800" title="{{ $invoice->reservation->tenant->phone }}">
                                            <x-icons.phone />
                                        </a>
                                    @endif
                                    <a href="mailto:{{ $invoice->reservation->tenant->invoiceEmail() }}" onclick="event.stopPropagation()"
                                       class="text-blue-600 hover:text-blue-800" title="{{ $invoice->reservation->tenant->invoiceEmail() }}">
                                        <i class="fa-regular fa-envelope"></i>
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
                                        <form method="POST" action="{{ route('invoices.remind', [$invoice] + redirect_back_params()) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="link-primary" onclick="return confirm('{{ __('Send a payment reminder?') }}')">
                                                {{ __('Reminder') }}
                                            </button>
                                        </form>
                                    @endif

                                    @if(!in_array($computedStatus, [\App\Enums\InvoiceStatus::PAID, \App\Enums\InvoiceStatus::CANCELLED]))
                                        <form method="POST" action="{{ route('invoices.pay', [$invoice] + redirect_back_params()) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="link-success" onclick="return confirm('{{ __('Mark this invoice as paid?') }}')">
                                                {{ __('Paid') }}
                                            </button>
                                        </form>

                                        <button type="button"
                                                onclick="openCancelModal({{ $invoice->id }})"
                                                class="link-danger">
                                            {{ __('Cancel') }}
                                        </button>
                                    @endif

                                    @if($invoice->canRecreate())
                                        <button type="button"
                                                onclick="openRecreateModal({{ $invoice->id }})"
                                                class="link-primary">
                                            {{ __('Recreate') }}
                                        </button>
                                    @endif
                                @else
                                    {{-- Actions for user view --}}
                                    @if ($invoice->reminder_count)
                                        <a href="{{ route('reservations.reminder.pdf', $invoice->reservation->hash) }}"
                                           target="_blank"
                                           class="link-primary">
                                            {{ __('View PDF') }}
                                        </a>
                                    @else
                                        <a href="{{ route('reservations.invoice.pdf', $invoice->reservation->hash) }}"
                                           target="_blank"
                                           class="link-primary">
                                            {{ __('View PDF') }}
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
                                    <span class="text-gray-500">{{ __('Issue date') }}:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->first_issued_at?->format('d.m.Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">{{ __('Due date') }}:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->due_at?->format('d.m.Y') ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">{{ __('Reminders') }}:</span>
                                    <span class="ml-1 text-gray-900">{{ $invoice->reminder_count }}</span>
                                </div>
                                @if($invoice->paid_at)
                                    <div>
                                        <span class="text-gray-500">{{ __('Paid on') }}:</span>
                                        <span class="ml-1 text-gray-900">{{ $invoice->paid_at->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                                @if($invoice->cancelled_at)
                                    <div>
                                        <span class="text-gray-500">{{ __('Cancelled on') }}:</span>
                                        <span class="ml-1 text-gray-900">{{ $invoice->cancelled_at->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                            {{ __('No invoices found') }}
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Cancel the invoice') }}</h3>
            <form id="cancel-form" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_email" value="1" checked
                               id="cancel-send-email"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ __('Send an email to the debtor') }}</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="cancel-reason" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Cancellation reason (optional)') }}
                    </label>
                    <textarea name="reason"
                              id="cancel-reason"
                              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="3"
                              placeholder="{{ __('Explain the reason for cancellation...') }}"></textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ __('This reason will be included in the email if the box above is checked.') }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeCancelModal()"
                            class="btn btn-secondary">
                        {{ __('Back') }}
                    </button>
                    <button type="submit" class="btn btn-delete">
                        {{ __('Confirm cancellation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de recréation de facture -->
    <div id="recreate-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Recreate the invoice') }}</h3>
            <form id="recreate-form" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_email" value="1" checked
                               id="recreate-send-email"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ __('Send an email to the debtor') }}</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="recreate-reason" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Explanation (optional)') }}
                    </label>
                    <textarea name="reason"
                              id="recreate-reason"
                              class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="3"
                              placeholder="{{ __('Add an explanation...') }}"></textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ __('This explanation will be included in the email if the box above is checked.') }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeRecreateModal()"
                            class="btn btn-secondary">
                        {{ __('Back') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Recreate the invoice') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const invoiceRedirectQuery = @json(http_build_query(redirect_back_params()));

        function openCancelModal(invoiceId) {
            const query = invoiceRedirectQuery ? '?' + invoiceRedirectQuery : '';
            document.getElementById('cancel-form').action = '/invoices/' + invoiceId + '/cancel' + query;
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
            const query = invoiceRedirectQuery ? '?' + invoiceRedirectQuery : '';
            document.getElementById('recreate-form').action = '/invoices/' + invoiceId + '/recreate' + query;
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
