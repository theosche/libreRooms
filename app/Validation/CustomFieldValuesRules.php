<?php

namespace App\Validation;

use App\Enums\CustomFieldTypes;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Validation\Rule;

class CustomFieldValuesRules
{
    protected static function rulesForCustomField(CustomField|CustomFieldValue $field): array
    {
        $rules = [];
        $rules[] = $field->required ? 'required' : 'nullable';

        switch ($field->type) {
            case CustomFieldTypes::TEXT->value:
            case CustomFieldTypes::TEXTAREA->value:
                $rules[] = 'string';
                break;

            case CustomFieldTypes::SELECT->value:
            case CustomFieldTypes::RADIO->value:
                $rules[] = 'string';
                $rules[] = Rule::in($field->options);
                break;

            case CustomFieldTypes::CHECKBOX->value:
                $rules[] = 'array';
                $rules[] = function ($attr, $value, $fail) use ($field) {
                    foreach ($value ?? [] as $v) {
                        if (! in_array($v, $field->options, true)) {
                            $fail("Valeur invalide pour {$field->label}");
                        }
                    }
                };
                break;
        }
        return $rules;
    }

    public static function createRules(Room $room): array
    {
        $rules = [];
        foreach ($room->customFields->where('active', true) as $field) {
            $rules[$field->key] = self::rulesForCustomField($field);
        }
        return $rules;
    }

    public static function updateRules(Reservation $reservation): array
    {
        $rules = [];
        foreach ($reservation->customFieldValues as $field) {
            $rules[$field->key] = self::rulesForCustomField($field);
        }
        return $rules;
    }

}
