@php
    $timezone = $room->getTimezone();
    $useFreePrice = $room->price_mode == App\Enums\PriceModes::FREE;
@endphp
<table class="events-table">
    <thead>
        <tr>
            <th class="date-col">{{ __('Start') }}</th>
            <th class="date-col">{{ __('End') }}</th>
            <th>{{ __('Options') }}</th>
            @if(!$useFreePrice)
                <th class="price-col">{{ __('Price') }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($reservation->events as $event)
            @php
                $startInTimezone = $event->start->copy()->setTimezone($timezone);
                $endInTimezone = $event->end->copy()->setTimezone($timezone);
            @endphp
            <tr>
                <td class="date-col">{{ $startInTimezone->format('d.m.Y - H:i') }}</td>
                <td class="date-col">{{ $endInTimezone->format('d.m.Y - H:i') }}</td>
                <td>{{ $event->price_label }}</td>
                @if(!$useFreePrice)
                   <td class="price-col">{{ currency($event->price, $owner) }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
