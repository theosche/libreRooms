@extends('emails.layout')

@section('content')
    <h1>Annulation de votre réservation</h1>

    <p>Bonjour,</p>

    <p>
        Vous avez fait une demande de réservation de la salle <em>{{ $room->name }}</em> pour l'événement/activité
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

    <div class="warning-box">
        <strong>Raison de l'annulation :</strong><br>
        {{ $complement }}
    </div>

    <p>
        Nous vous remercions pour votre compréhension. Pour toute question, n'hésitez pas à nous contacter en réponse à cet email.
    </p>

    <p>Avec nos meilleures salutations,</p>
@endsection
