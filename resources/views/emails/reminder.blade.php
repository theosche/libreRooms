@extends('emails.layout')

@section('content')
    <h1>{{ __('Payment reminder') }}{{ $invoice->reminder_count == $owner->max_nb_reminders ? " (" . __('Final reminder') . ")" : "" }}</h1>

    <p>{{ __('Hello') }},</p>

    <div class="warning-box">
        <strong>{{ $invoice->formattedReminderCount() }}</strong> - {{ __('Invoice no.') }} {{ $invoice->number }}
    </div>

    <p>
        {{ __('You rented the room :room for the event/activity :title.', ['room' => $room->name, 'title' => $reservation->title]) }}
    </p>

    <h2>{{ $reservation->events->count() > 1 ? __('Reserved dates') : __('Reserved date') }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->dateString() }}
            </li>
        @endforeach
    </ul>

    <p>
        {{ __('Unless we are mistaken, we have not yet received your payment for invoice no. :number dated :date.', ['number' => $invoice->number, 'date' => $invoice->first_issued_at->format('d.m.Y')]) }}
    </p>

    <div class="highlight-box">
        <p style="margin: 0;">
            <strong>{{ __('Amount due:') }}</strong> {{ currency($invoice->amount, $room->owner) }}<br>
            <strong>{{ __('New due date:') }}</strong> {{ $invoice->due_at->format('d.m.Y') }}
        </p>
    </div>

    <p>
        <a href="{{ route('reservations.reminder.pdf', $reservation->hash) }}" class="btn">{{ __('Download the reminder') }}</a>
    </p>

    <p>
        {{ __('Thank you in advance for your payment. For any questions, feel free to contact us by replying to this email.') }}
    </p>

    <p>{{ __('Best regards,') }}</p>
@endsection
