@extends('emails.layout')

@section('content')
    <h1>{{ __('Cancellation of your reservation') }}</h1>

    <p>{{ __('Hello') }},</p>

    <p>
        {{ __('You made a reservation request for the room :room for the event/activity :title.', ['room' => $room->name, 'title' => $reservation->title]) }}
    </p>

    <h2>{{ $reservation->events->count() > 1 ? __('Requested dates') : __('Requested date') }}</h2>
    <ul>
        @foreach ($reservation->events as $event)
            <li>
                {{ $event->dateString() }}
            </li>
        @endforeach
    </ul>

    <div class="warning-box">
        <strong>{{ __('Reason for cancellation:') }}</strong><br>
        {{ $complement }}
    </div>

    <p>
        {{ __('Thank you for your understanding. For any questions, feel free to contact us by replying to this email.') }}
    </p>

    <p>{{ __('Best regards,') }}</p>
@endsection
