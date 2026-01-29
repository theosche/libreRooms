@extends('layouts.app')

@php
    $isCreate = request()->routeIs('reservations.create');
    $isEdit = request()->routeIs('reservations.edit');
    $isAdmin = auth()->user()?->can('manageReservations', $room);
@endphp

@section('title', ($isCreate ? 'Nouvelle réservation' : 'Modifier la réservation') . ' - ' . $room->name)

@section('page-script')
    @vite(['resources/js/reservations/reservation-form.js'])
    <script>
        window.RoomConfig = @json($roomConfig);
        window.EnabledDiscounts = @json($enabledDiscounts);
        window.ResEvents = @json($events);
        @php
        $contactsArray = $contacts->map->only([
            'id',
            'type',
            'first_name',
            'last_name',
            'entity_name',
            'email',
            'invoice_email',
            'phone',
            'street',
            'zip',
            'city',
        ]);
        @endphp
        window.Contacts = @json($contactsArray);
    </script>
@endsection

@section('content')
        <div class="container-full-form">
            <div class="form-header">
                <h1 class="form-title">{{ $isCreate ? 'Nouvelle réservation' : 'Modifier la réservation' }}</h1>
                <p class="form-subtitle">{{ $room->name }}</p>
            </div>
            <form method="POST" class="reservation-form styled-form"
                  action="{{$isCreate ? route('reservations.store', $room) : ($isEdit ? route('reservations.update', $reservation) : "")}}">
        @if ($isEdit)
            @method('PUT')
        @endif
        @csrf

        @if($errors->any())
            <ul class="px-4 py-2 bg-red-100">
                @foreach($errors->all() as $error)
                    <li class="my-2 text-red-500">{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- 1. Contact --}}
        @include('reservations.partials.contact',['contacts'=>$contacts,'tenant'=>$reservation?->tenant])

        {{-- 2. Custom fields --}}
        @include('reservations.partials.custom-fields',['customFields' => $room->customFields->where('active', true),
                                                        'customFieldValues' => $reservation?->customFieldValues])

        {{-- 3. Discounts --}}
        @include('reservations.partials.discounts',[
            'discounts' => $room->discounts->where('active', true),
            'enabledDiscounts' => $enabledDiscounts,
            'tenantType' => $reservation?->tenant->type,
            'owner' => $room->owner,
            ])

        {{-- 4. Event infos --}}
        @include('reservations.partials.event-info',[
            'title' => $reservation?->title,
            'description' => $reservation?->description,
            ])

        {{-- 4.5. Calendar --}}
        @if($room->embed_calendar_mode === App\Enums\EmbedCalendarModes::ADMIN_ONLY && $isAdmin ||
            $room->embed_calendar_mode === App\Enums\EmbedCalendarModes::ENABLED)
            <div class="form-group">
                <h3 class="form-group-title">Calendrier des disponibilités</h3>
                <div class="form-element">
                    <div class="form-field">
                        @include('rooms._calendar', ['room' => $room])
                    </div>
                </div>
            </div>
        @endif

        {{-- 5. Events --}}
        @include('reservations.partials.events', [
            'availableOptions' => $room->options->where('active',true),
            'events' => $events,
            'owner' => $room->owner,
            ])

        {{-- 6. Donation --}}
        @if ($room->use_donation && !($room->price_mode == App\Enums\PriceModes::FREE))
            @include('reservations.partials.donation',[
            'reservationDonation' => $reservation?->donation,
            'currency' => $roomConfig['settings']['currency'],
            ])
        @endif

        {{-- 7. Special discount --}}
        @if ($room->use_special_discount && $isAdmin && !($room->price_mode == App\Enums\PriceModes::FREE))
            @include('reservations.partials.special-discount',[
            'specialDiscount' => $reservation?->special_discount,
            'currency' => $roomConfig['settings']['currency'],
            ])
        @endif

        {{-- 8. Price summary --}}
        @include('reservations.partials.price-summary', [
            'discounts' => $room->discounts->where('active', true),
            'reservationDiscounts' => $reservation?->discounts->modelKeys() ?? [],
            'specialDiscount' => $reservation?->special_discount ?? 0,
            'donation' => $reservation?->donation ?? 0,
            'useFreePrice' => $room->price_mode == App\Enums\PriceModes::FREE,
            'owner' => $room->owner,
            ])

        {{-- 9. Free price --}}
        @if ($room->price_mode == App\Enums\PriceModes::FREE)
            @include('reservations.partials.free-price',[
            'freePrice' => $reservation?->donation,
            'currency' => $roomConfig['settings']['currency'],
            ])
        @endif

        {{-- 10. Charter --}}
        @include('reservations.partials.charter',[
        'charter_mode' => $room->charter_mode,
        'charter_str' => $room->charter_str,
        'isCreate' => $isCreate,
        ])

        {{-- 11. Custom message --}}
        @if ($isAdmin)
            @include('reservations.partials.custom-message', ['customMessage' => $reservation?->custom_message])
        @endif

            <a class="btn btn-secondary" href="{{ url()->previous() }}">Annuler</a>
        @if ($isCreate)
            <button type="submit" class="btn btn-primary" name="action" value="prepare">Envoyer la demande</button>
        @elseif ($isEdit)
            <button type="submit" class="btn btn-primary" name="action" value="prepare">Modifier la demande</button>
        @endif
        @if ($isAdmin && $isCreate)
            <button type="submit" class="btn btn-confirm" name="action" value="confirm">Valider directement la demande</button>
        @elseif ($isAdmin && $isEdit)
            <button type="submit" class="btn btn-confirm" name="action" value="confirm">Valider la demande</button>
        @endif
        @if($isEdit && $reservation->status !== App\Enums\ReservationStatus::CANCELLED)
            <button type="button" onclick="openCancelModal()" class="btn btn-delete">
                Annuler la demande
            </button>
        @endif
    </form>
    </div>

    <!-- Modal de chargement -->
    <div id="loader-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
            <p class="text-gray-700 font-medium">Traitement en cours...</p>
        </div>
    </div>

    @if($isEdit && $reservation->status !== App\Enums\ReservationStatus::CANCELLED)
        <!-- Modal d'annulation -->
        <div id="cancel-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Annuler la réservation</h3>
                <form id="cancel-form" method="POST" action="{{ route('reservations.cancel', $reservation) }}">
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
            function openCancelModal() {
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
                if (window.showLoaderModal) {
                    window.showLoaderModal();
                }
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
    @endif
@endsection
