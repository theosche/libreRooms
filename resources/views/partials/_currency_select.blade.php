{{--
    Currency select field
    Props:
    - $name: input name
    - $id: input id
    - $value: current value (nullable)
    - $defaultCurrency: default currency to show in placeholder (optional)
    - $required: whether field is required (default: false)
    - $showDefaultOption: show "Paramètres par défaut" option (default: true)
--}}

@php
    $currencies = [
        'Europe' => [
            'CHF' => 'CHF - Franc suisse',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - Livre sterling',
            'SEK' => 'SEK - Couronne suédoise',
            'NOK' => 'NOK - Couronne norvégienne',
            'DKK' => 'DKK - Couronne danoise',
            'PLN' => 'PLN - Zloty polonais',
            'CZK' => 'CZK - Couronne tchèque',
            'HUF' => 'HUF - Forint hongrois',
            'RON' => 'RON - Leu roumain',
            'BGN' => 'BGN - Lev bulgare',
        ],
        'Amérique' => [
            'USD' => 'USD - Dollar américain',
            'CAD' => 'CAD - Dollar canadien',
            'MXN' => 'MXN - Peso mexicain',
            'BRL' => 'BRL - Real brésilien',
            'ARS' => 'ARS - Peso argentin',
        ],
        'Asie & Pacifique' => [
            'JPY' => 'JPY - Yen japonais',
            'CNY' => 'CNY - Yuan chinois',
            'HKD' => 'HKD - Dollar de Hong Kong',
            'SGD' => 'SGD - Dollar de Singapour',
            'AUD' => 'AUD - Dollar australien',
            'NZD' => 'NZD - Dollar néo-zélandais',
            'INR' => 'INR - Roupie indienne',
            'KRW' => 'KRW - Won sud-coréen',
        ],
        'Afrique & Moyen-Orient' => [
            'ZAR' => 'ZAR - Rand sud-africain',
            'AED' => 'AED - Dirham des EAU',
            'ILS' => 'ILS - Shekel israélien',
        ],
    ];

    $showDefaultOption = $showDefaultOption ?? true;
    $required = $required ?? false;
@endphp

<select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif>
    @if($showDefaultOption)
        <option value="">
            Paramètres par défaut
            @if(isset($defaultCurrency))
                ({{ $defaultCurrency }})
            @endif
        </option>
    @endif

    @foreach($currencies as $region => $regionCurrencies)
        <optgroup label="{{ $region }}">
            @foreach($regionCurrencies as $code => $label)
                <option value="{{ $code }}" @selected(old($name, $value) == $code)>
                    {{ $label }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
