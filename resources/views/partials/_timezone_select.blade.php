{{--
    Timezone select field
    Props:
    - $name: input name
    - $id: input id
    - $value: current value (nullable)
    - $defaultTimezone: default timezone to show in placeholder (optional)
    - $required: whether field is required (default: false)
    - $showDefaultOption: show "Paramètres par défaut" option (default: true)
--}}

@php
    $commonTimezones = [
        'Europe' => [
            'Europe/Zurich' => 'Europe/Zurich (GMT+1/+2)',
            'Europe/Paris' => 'Europe/Paris (GMT+1/+2)',
            'Europe/Berlin' => 'Europe/Berlin (GMT+1/+2)',
            'Europe/Brussels' => 'Europe/Brussels (GMT+1/+2)',
            'Europe/London' => 'Europe/London (GMT+0/+1)',
            'Europe/Madrid' => 'Europe/Madrid (GMT+1/+2)',
            'Europe/Rome' => 'Europe/Rome (GMT+1/+2)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (GMT+1/+2)',
            'Europe/Vienna' => 'Europe/Vienna (GMT+1/+2)',
            'Europe/Stockholm' => 'Europe/Stockholm (GMT+1/+2)',
            'Europe/Copenhagen' => 'Europe/Copenhagen (GMT+1/+2)',
            'Europe/Oslo' => 'Europe/Oslo (GMT+1/+2)',
            'Europe/Helsinki' => 'Europe/Helsinki (GMT+2/+3)',
            'Europe/Athens' => 'Europe/Athens (GMT+2/+3)',
            'Europe/Warsaw' => 'Europe/Warsaw (GMT+1/+2)',
            'Europe/Prague' => 'Europe/Prague (GMT+1/+2)',
            'Europe/Budapest' => 'Europe/Budapest (GMT+1/+2)',
            'Europe/Bucharest' => 'Europe/Bucharest (GMT+2/+3)',
            'Europe/Sofia' => 'Europe/Sofia (GMT+2/+3)',
            'Europe/Lisbon' => 'Europe/Lisbon (GMT+0/+1)',
        ],
        'America' => [
            'America/New_York' => 'America/New York (GMT-5/-4)',
            'America/Chicago' => 'America/Chicago (GMT-6/-5)',
            'America/Denver' => 'America/Denver (GMT-7/-6)',
            'America/Los_Angeles' => 'America/Los Angeles (GMT-8/-7)',
            'America/Toronto' => 'America/Toronto (GMT-5/-4)',
            'America/Montreal' => 'America/Montreal (GMT-5/-4)',
            'America/Mexico_City' => 'America/Mexico City (GMT-6/-5)',
            'America/Sao_Paulo' => 'America/São Paulo (GMT-3)',
            'America/Buenos_Aires' => 'America/Buenos Aires (GMT-3)',
        ],
        'Asia' => [
            'Asia/Tokyo' => 'Asia/Tokyo (GMT+9)',
            'Asia/Shanghai' => 'Asia/Shanghai (GMT+8)',
            'Asia/Hong_Kong' => 'Asia/Hong Kong (GMT+8)',
            'Asia/Singapore' => 'Asia/Singapore (GMT+8)',
            'Asia/Dubai' => 'Asia/Dubai (GMT+4)',
            'Asia/Kolkata' => 'Asia/Kolkata (GMT+5:30)',
            'Asia/Bangkok' => 'Asia/Bangkok (GMT+7)',
            'Asia/Seoul' => 'Asia/Seoul (GMT+9)',
        ],
        'Pacific' => [
            'Pacific/Auckland' => 'Pacific/Auckland (GMT+12/+13)',
            'Pacific/Sydney' => 'Pacific/Sydney (GMT+10/+11)',
            'Pacific/Fiji' => 'Pacific/Fiji (GMT+12/+13)',
        ],
        'Africa' => [
            'Africa/Cairo' => 'Africa/Cairo (GMT+2)',
            'Africa/Johannesburg' => 'Africa/Johannesburg (GMT+2)',
            'Africa/Lagos' => 'Africa/Lagos (GMT+1)',
            'Africa/Nairobi' => 'Africa/Nairobi (GMT+3)',
        ],
        'UTC' => [
            'UTC' => 'UTC (GMT+0)',
        ],
    ];

    $showDefaultOption = $showDefaultOption ?? true;
    $required = $required ?? false;
@endphp

<select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif>
    @if($showDefaultOption)
        <option value="">
            Paramètres par défaut
            @if(isset($defaultTimezone))
                ({{ $defaultTimezone }})
            @endif
        </option>
    @endif

    @foreach($commonTimezones as $region => $timezones)
        <optgroup label="{{ $region }}">
            @foreach($timezones as $tzValue => $tzLabel)
                <option value="{{ $tzValue }}" @selected(old($name, $value) == $tzValue)>
                    {{ $tzLabel }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>