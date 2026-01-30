@if ($customFields->isNotEmpty())
    <div class="form-group" id="custom-fields-form-group">
    <h3 class="form-group-title">Questions supplémentaires</h3>
    @foreach($customFields as $field)
        @php
            $value = old($field->key) ?? ($customFieldValues ? \App\Models\CustomFieldValue::getMatchingValue($customFieldValues, $field) : "");
        @endphp
        <fieldset class="form-element">
            @switch($field->type)
                @case(App\Enums\CustomFieldTypes::TEXT)
                    <div class="form-field">
                        <label for="{{ $field->key }}" class="form-element-title">{{ $field->label . ($field->required ? " *" : "") }}</label>
                        <input
                            type="text"
                            id="{{ $field->key }}"
                            name="{{ $field->key }}"
                            value="{{ $value }}"
                            @required($field->required)
                        >
                    </div>
                    @break
                @case(App\Enums\CustomFieldTypes::TEXTAREA)
                    <div class="form-field">
                        <label for="{{ $field->key }}" class="form-element-title">{{ $field->label . ($field->required ? " *" : "") }}:</label>
                        <textarea
                            rows="5"
                            id="{{ $field->key }}"
                            name="{{ $field->key }}"
                            @required($field->required)
                        >{{ $value }}</textarea>
                    </div>
                    @break
                @case(App\Enums\CustomFieldTypes::SELECT)
                    <div class="form-field">
                        <label for="{{ $field->key }}" class="form-element-title">{{ $field->label . ($field->required ? " *" : "") }}</label>
                        <select name="{{ $field->key }}" id="{{ $field->key }}">
                        @foreach($field->options as $optionKey=>$option)
                            <option value="{{ $optionKey }}" @selected($value == $optionKey)>
                                {{ $option }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                    @break
                @case(App\Enums\CustomFieldTypes::RADIO)
                    <legend class="form-element-title">{{ $field->label . ($field->required ? " *" : "") }}</legend>
                    <div class="form-element-row">
                    @foreach($field->options as $optionKey=>$option)
                        <div class="form-field">
                            <input
                                type="radio"
                                id="{{ $field->key . '_' . $optionKey }}"
                                name="{{ $field->key }}"
                                value="{{ $optionKey }}"
                                @checked($value == $optionKey)
                            >
                            <label for="{{ $field->key . '_' . $optionKey }}">{{ $option }}</label>
                        </div>
                    @endforeach
                    </div>
                    @break
                @case(App\Enums\CustomFieldTypes::CHECKBOX)
                    <legend class="form-element-title">{{ $field->label . ($field->required ? " *" : "") }}</legend>
                    <div class="form-element-row">
                    @foreach($field->options as $optionKey=>$option)
                        <div class="form-field">
                            <input
                                type="checkbox"
                                id="{{ $field->key . '_' . $optionKey }}"
                                name="{{ $field->key }}[]"
                                value="{{ $optionKey }}"
                                @checked($value && in_array($optionKey,$value))
                            >
                            <label for="{{ $field->key . '_' . $optionKey }}">{{ $option }}</label>
                        </div>
                    @endforeach
                    </div>
                    @break
            @endswitch
            @error($field->key)
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </fieldset>
    @endforeach
    </div>
@endif
