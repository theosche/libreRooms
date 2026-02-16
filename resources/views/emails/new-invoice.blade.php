@extends('emails.layout')

@section('content')
    <h1>{{ __('New invoice') }}</h1>

    <p>{{ __('Hello') }},</p>

    <p>
        {{ __('A new invoice has been created for your reservation of the room :room for the event :title.', ['room' => $room->name, 'title' => $reservation->title]) }}
    </p>

    @if($complement)
        <div class="highlight-box">
            <strong>{{ __('Explanation:') }}</strong><br>
            {{ $complement }}
        </div>
    @endif

    <h2>{{ $reservation->events->count() > 1 ? __('Reserved dates') : __('Reserved date') }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->dateString() }}
                <a href="{{ route('reservations.event-ics', ['hash' => $reservation->hash, 'uid' => $event->uid]) }}" style="font-size: 12px;">(ics)</a>
            </li>
        @endforeach
    </ul>

    <h2>{{ __('Invoice') }}</h2>
    <div class="highlight-box">
        <p style="margin: 0;">
            <strong>{{ __('Amount due:') }}</strong> {{ currency($invoice->amount, $room->owner) }}
        </p>
        <p style="margin: 8px 0 0 0; font-size: 14px; color: #6b7280;">
            {{ __('Due date:') }} {{ $invoice->due_at->format('d.m.Y') }}
            <small>({{ $owner->invoice_due_mode->label($owner->invoice_due_days) }})</small>
        </p>
    </div>
    <p>
        <a href="{{ route('reservations.invoice.pdf', $reservation->hash) }}" class="btn">{{ __('Download the invoice') }}</a>
    </p>

    <p>{{ __('For any questions, feel free to contact us by replying to this email.') }}</p>

    <p>{{ __('Best regards,') }}</p>
@endsection
