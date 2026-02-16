@extends('emails.layout')

@section('content')
    <h1>{{ __('Cancellation of your invoice no. :number', ['number' => $invoice->number]) }}</h1>

    <p>{{ __('Hello') }},</p>

    <p>
        {{ __('You had received an invoice related to a reservation of the room :room for the event/activity :title.', ['room' => $room->name, 'title' => $reservation->title]) }}
    </p>

    <h2>{{ $reservation->events->count() > 1 ? __('Requested dates') : __('Requested date') }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->dateString() }}
            </li>
        @endforeach
    </ul>

    <p>
        {{ __('We inform you that this invoice has been cancelled.') }}
    </p>

    @if($complement)
        <div class="warning-box">
            <strong>{{ __('Reason for cancellation:') }}</strong><br>
            {{ $complement }}
        </div>
    @endif

    <p>{{ __('Best regards,') }}</p>
@endsection
