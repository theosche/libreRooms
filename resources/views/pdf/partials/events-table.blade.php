@php
    $timezone = $room->getTimezone();
@endphp
<table class="events-table">
    <thead>
        <tr>
            <th class="date-col">Début</th>
            <th class="date-col">Fin</th>
            <th>Options</th>
            <th class="price-col">Prix</th>
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
                <td class="price-col">{{ currency($event->price, $owner) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
