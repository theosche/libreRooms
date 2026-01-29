@extends('emails.layout')

@section('content')
    <h1>Rappel de paiement{{ $invoice->reminder_count == $owner->max_nb_reminders ? " (Dernier rappel)" : "" }}</h1>

    <p>Bonjour,</p>

    <div class="warning-box">
        <strong>{{ $invoice->formatedReminderCount() }}</strong> - Facture n° {{ $invoice->number }}
    </div>

    <p>
        Vous avez loué la salle <em>{{ $room->name }}</em> pour l'événement/activité
        <em>{{ $reservation->title }}</em>.
    </p>

    <h2>{{ $reservation->events->count() > 1 ? 'Dates réservées' : 'Date réservée' }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->startLocalTz()->format('d.m.Y - H:i') }} au {{ $event->endLocalTz()->format('d.m.Y - H:i') }}
            </li>
        @endforeach
    </ul>

    <p>
        Sauf erreur de notre part, nous n'avons pas encore reçu votre paiement pour la facture n°
        {{ $invoice->number }} du {{ $invoice->first_issued_at->format('d.m.Y') }}.
    </p>

    <div class="highlight-box">
        <p style="margin: 0;">
            <strong>Montant dû :</strong> {{ currency($invoice->amount, $room->owner) }}<br>
            <strong>Nouvelle échéance :</strong> {{ $invoice->due_at->format('d.m.Y') }}
        </p>
    </div>

    <p>
        <a href="{{ route('reservations.reminder.pdf', $reservation->hash) }}" class="btn">Télécharger le rappel</a>
    </p>

    <p>
        Nous vous remercions d'avance pour votre paiement. Pour toute question, n'hésitez pas à nous contacter en réponse à cet email.
    </p>

    <p>Avec nos meilleures salutations,</p>
@endsection
