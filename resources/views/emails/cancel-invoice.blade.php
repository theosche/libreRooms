@extends('emails.layout')

@section('content')
    <h1>Annulation de votre facture n° {{ $invoice->number }}</h1>

    <p>Bonjour,</p>

    <p>
        Vous aviez reçu une facture en lien avec une réservation de la salle <em>{{ $room->name }}</em> pour l'événement/activité
        <em>{{ $reservation->title }}</em>.
    </p>

    <h2>{{ $reservation->events->count() > 1 ? 'Dates demandées' : 'Date demandée' }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->startLocalTz()->format('d.m.Y - H:i') }} au {{ $event->endLocalTz()->format('d.m.Y - H:i') }}
            </li>
        @endforeach
    </ul>

    <p>
        Nous vous informons que cette facture est annulée.
    </p>

    @if($complement)
        <div class="warning-box">
            <strong>Raison de l'annulation :</strong><br>
            {{ $complement }}
        </div>
    @endif

    <p>Avec nos meilleures salutations,</p>
@endsection
