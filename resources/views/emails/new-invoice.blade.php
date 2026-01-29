@extends('emails.layout')

@section('content')
    <h1>Nouvelle facture</h1>

    <p>Bonjour,</p>

    <p>
        Une nouvelle facture a été créée pour votre réservation de la salle <em>{{ $room->name }}</em> pour l'événement <strong>{{ $reservation->title }}</strong>.
    </p>

    @if($complement)
        <div class="highlight-box">
            <strong>Explication :</strong><br>
            {{ $complement }}
        </div>
    @endif

    <h2>{{ $reservation->events->count() > 1 ? 'Dates réservées' : 'Date réservée' }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->startLocalTz()->format('d.m.Y - H:i') }} au {{ $event->endLocalTz()->format('d.m.Y - H:i') }}
                <a href="{{ route('reservations.event-ics', ['hash' => $reservation->hash, 'uid' => $event->uid]) }}" style="font-size: 12px;">(ics)</a>
            </li>
        @endforeach
    </ul>

    <h2>Facture</h2>
    <div class="highlight-box">
        <p style="margin: 0;">
            <strong>Montant dû :</strong> {{ currency($invoice->amount, $room->owner) }}
        </p>
        <p style="margin: 8px 0 0 0; font-size: 14px; color: #6b7280;">
            Échéance : {{ $invoice->due_at->format('d.m.Y') }}
            <small>({{ $owner->invoice_due_mode->label($owner->invoice_due_days) }})</small>
        </p>
    </div>
    <p>
        <a href="{{ route('reservations.invoice.pdf', $reservation->hash) }}" class="btn">Télécharger la facture</a>
    </p>

    <p>Pour toute question, n'hésitez pas à nous contacter en réponse à cet email.</p>

    <p>Avec nos meilleures salutations,</p>
@endsection
