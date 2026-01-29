{{--
    Locale select field
    Props:
    - $name: input name
    - $id: input id
    - $value: current value (nullable)
    - $defaultLocale: default locale to show in placeholder (optional)
    - $required: whether field is required (default: false)
    - $showDefaultOption: show "Paramètres par défaut" option (default: true)
--}}

@php
    $locales = [
        'Français' => [
            'fr_FR' => 'Français (France)',
            'fr_CH' => 'Français (Suisse)',
            'fr_BE' => 'Français (Belgique)',
            'fr_CA' => 'Français (Canada)',
            'fr_LU' => 'Français (Luxembourg)',
        ],
        'Deutsch' => [
            'de_DE' => 'Deutsch (Deutschland)',
            'de_CH' => 'Deutsch (Schweiz)',
            'de_AT' => 'Deutsch (Österreich)',
            'de_LU' => 'Deutsch (Luxemburg)',
        ],
        'English' => [
            'en_GB' => 'English (United Kingdom)',
            'en_US' => 'English (United States)',
            'en_AU' => 'English (Australia)',
            'en_CA' => 'English (Canada)',
            'en_IE' => 'English (Ireland)',
        ],
        'Italiano' => [
            'it_IT' => 'Italiano (Italia)',
            'it_CH' => 'Italiano (Svizzera)',
        ],
        'Español' => [
            'es_ES' => 'Español (España)',
            'es_MX' => 'Español (México)',
            'es_AR' => 'Español (Argentina)',
        ],
        'Português' => [
            'pt_PT' => 'Português (Portugal)',
            'pt_BR' => 'Português (Brasil)',
        ],
        'Nederlands' => [
            'nl_NL' => 'Nederlands (Nederland)',
            'nl_BE' => 'Nederlands (België)',
        ],
        'Autres' => [
            'pl_PL' => 'Polski (Polska)',
            'cs_CZ' => 'Čeština (Česká republika)',
            'hu_HU' => 'Magyar (Magyarország)',
            'ro_RO' => 'Română (România)',
            'sv_SE' => 'Svenska (Sverige)',
            'da_DK' => 'Dansk (Danmark)',
            'nb_NO' => 'Norsk (Norge)',
            'fi_FI' => 'Suomi (Suomi)',
            'el_GR' => 'Ελληνικά (Ελλάδα)',
        ],
    ];

    $showDefaultOption = $showDefaultOption ?? true;
    $required = $required ?? false;
@endphp

<select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif>
    @if($showDefaultOption)
        <option value="">
            Paramètres par défaut
            @if(isset($defaultLocale))
                ({{ $defaultLocale }})
            @endif
        </option>
    @endif

    @foreach($locales as $language => $regionLocales)
        <optgroup label="{{ $language }}">
            @foreach($regionLocales as $code => $label)
                <option value="{{ $code }}" @selected(old($name, $value) == $code)>
                    {{ $label }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
