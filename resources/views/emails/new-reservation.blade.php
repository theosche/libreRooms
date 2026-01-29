@extends('emails.layout')

@section('content')
    <h1>Nouvelle demande de réservation</h1>

    <p>
        Une demande de réservation a été faite par
        <em>{{ $reservation->tenant->display_name() }}</em> pour la salle
        <em>{{ $room->name }}</em>.
    </p>

    <div class="highlight-box">
        <strong>{{ $reservation->title }}</strong>
        @if($reservation->description)
            <br><span style="color: #6b7280;">{{ $reservation->description }}</span>
        @endif
    </div>

    <h2>Dates demandées</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->startLocalTz()->format('d.m.Y - H:i') }} au {{ $event->endLocalTz()->format('d.m.Y - H:i') }}
                <a href="{{ route('reservations.event-ics', ['hash' => $reservation->hash, 'uid' => $event->uid]) }}" style="font-size: 12px;">(ics)</a>
            </li>
        @endforeach
    </ul>

    <p>Merci de contrôler la demande pour la confirmer ou la refuser :</p>

    <p>
        <a href="{{ route('reservations.edit', $reservation) }}" class="btn">Voir la demande</a>
    </p>
@endsection
