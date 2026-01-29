@extends('emails.layout')

@section('content')
    <h1>Confirmation de votre réservation</h1>

    <p>Bonjour,</p>

    <p>
        Votre réservation de la salle <em>{{ $room->name }}</em> a été confirmée.
    </p>

    <div class="highlight-box">
        <strong>{{ $reservation->title }}</strong>
        @if($reservation->description)
            <br><span style="color: #6b7280;">{{ $reservation->description }}</span>
        @endif
    </div>

    <h2>{{ $reservation->events->count() > 1 ? 'Dates réservées' : 'Date réservée' }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->startLocalTz()->format('d.m.Y - H:i') }} au {{ $event->endLocalTz()->format('d.m.Y - H:i') }}
                <a href="{{ route('reservations.event-ics', ['hash' => $reservation->hash, 'uid' => $event->uid]) }}" style="font-size: 12px;">(ics)</a>
            </li>
        @endforeach
    </ul>

    @if ($room->custom_message)
        <h2>Informations importantes</h2>
        <p>{{ $room->custom_message }}</p>
    @endif

    @if ($reservation->custom_message)
        <div class="highlight-box">
            {{ $reservation->custom_message }}
        </div>
    @endif

    @if ($room->secret_message)
        <h2>Codes d'accès</h2>
        <p>
            Vous aurez besoin de codes d'accès. Comme ces informations peuvent changer, nous vous invitons à les vérifier peu avant votre événement :
        </p>
        <p>
            <a href="{{ route('reservations.codes', $reservation->hash) }}" class="btn btn-secondary">Voir les codes d'accès</a>
        </p>
    @endif

    <h2>Facture</h2>
    <div class="highlight-box">
        <p style="margin: 0;">
            <strong>Montant dû :</strong> {{ currency($invoice->amount, $room->owner) }}
            @if ($reservation->special_discount > 0)
                <br><span style="color: #059669;">Une réduction de {{ currency($reservation->special_discount, $room->owner) }} vous a été accordée.</span>
            @endif
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
