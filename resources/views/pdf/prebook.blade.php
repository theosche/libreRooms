@extends('pdf.layouts.base')

@section('title', 'Confirmation de pré-réservation')

@section('content')
    @include('pdf.partials.header', ['owner' => $owner])

    <div style="display: table; width: 100%;">
        <div style="display: table-cell; width: 50%;"></div>
        <div style="display: table-cell; width: 50%;">
            @include('pdf.partials.tenant-address', ['tenant' => $tenant])
        </div>
    </div>

    <h1 style="text-align: center; margin: 10mm 0;">Confirmation de pré-réservation de {{ $room->name }}</h1>

    @php
        $ownerContact = $owner->contact;
        $currentDate = now();
    @endphp

    <p>{{ $ownerContact->city }}, le {{ $currentDate->format('d.m.Y') }}</p>

    <div style="margin: 8mm 0;">
        <h2>{{ html_entity_decode($reservation->title) }}</h2>
        @if($reservation->description)
            <p>{{ html_entity_decode($reservation->description) }}</p>
        @endif
    </div>

    @include('pdf.partials.events-table', ['reservation' => $reservation, 'room' => $room, 'owner' => $owner])

    @include('pdf.partials.totals', ['reservation' => $reservation, 'owner' => $owner])

    <div class="message">
        <p>
            Votre demande de réservation de {{ $room->name }} a bien été reçue.
            La réservation doit être confirmée par {{ lcfirst($ownerContact->display_name()) }} avant d'être effective.
            Vous recevrez une confirmation et une facture par email.
            Si les délais sont courts et que vous ne recevez rien, contactez-nous !
        </p>
    </div>
@endsection
