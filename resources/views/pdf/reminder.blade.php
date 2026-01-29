@extends('pdf.layouts.base')

@section('title', 'Rappel - Facture ' . $invoice->number)

@section('content')
    @include('pdf.partials.header', ['owner' => $owner])
    <h1>FACTURE - {{ $invoice->formatedReminderCount() }}</h1>

    <div class="separator"></div>

    @php
        $ownerContact = $owner->contact;
        $invoiceDate = $invoice->issued_at;
        $dueDate = $invoice->due_at;
        $finalTotal = $reservation->finalPrice();
        $vatNumber = $owner->payment_instructions['vat_number'] ?? null;
    @endphp

    <div class="invoice-info">
        <div class="invoice-info-row">
            <div class="invoice-info-labels">
                <p>Facture n°:</p>
                <p>Date de la facture:</p>
                <p>Date du rappel:</p>
                <p>Nouvelle échéance:</p>
                <p>Montant dû:</p>
                <p>Numéro TVA:</p>
            </div>
            <div class="invoice-info-values">
                <p>{{ $invoice->number }}</p>
                <p>{{ $invoice->first_issued_at->format('d/m/Y') }}</p>
                <p>{{ $invoice->issued_at->format('d/m/Y') }}</p>
                <p>{{ $dueDate->format('d/m/Y') }}</p>
                <p>{{ currency($finalTotal, $owner) }}</p>
                <p>{{ $vatNumber ?? 'Pas enregistré à la TVA' }}</p>
            </div>
            <div class="invoice-info-tenant">
                @include('pdf.partials.tenant-address', ['tenant' => $tenant])
            </div>
        </div>
    </div>

    <div class="separator"></div>

    <div style="margin: 5mm 0;">
        <h2>Réservation de {{ $room->name }} - {{ html_entity_decode($reservation->title) }}</h2>
        @if($reservation->description)
            <p>Description de l'événement: {{ html_entity_decode($reservation->description) }}</p>
        @endif
    </div>

    @include('pdf.partials.events-table', ['reservation' => $reservation, 'room' => $room, 'owner' => $owner])

    @include('pdf.partials.totals', ['reservation' => $reservation, 'owner' => $owner])

    <div class="message">
        <p>
            Ce rappel fait suite à notre facture du {{ $invoice->first_issued_at->format('d/m/Y') }} qui reste impayée à ce jour.
            Nous vous prions de bien vouloir procéder au règlement avant le {{ $dueDate->format('d/m/Y') }}.
            Si vous avez déjà effectué le paiement, veuillez ne pas tenir compte de ce rappel.
            Pour toute question, n'hésitez pas à nous contacter.
        </p>
    </div>

    @if($paymentHtml)
        {!! $paymentHtml !!}
    @endif
@endsection
