<?php

namespace App\Validation;

use Illuminate\Validation\Rule;
use App\Enums\CustomFieldTypes;

class CustomFieldRules
{
    public static function rules($fieldId = null): array
    {
        $user = auth()->user();
        $userId = $user->id;
        $request = request();

        // Get room IDs that the user can use
        if ($user->is_global_admin) {
            $roomRule = Rule::exists('rooms', 'id');
        } else {
            // User must be admin of the room's owner
            $ownerIds = $user->owners()->wherePivot('role', 'admin')->pluck('owners.id');
            $roomRule = Rule::exists('rooms', 'id')->whereIn('owner_id', $ownerIds);
        }

        return [
            'room_id' => [
                'required',
                'integer',
                $roomRule,
            ],
            'label' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                Rule::in(array_map(fn ($case) => $case->value, CustomFieldTypes::cases())),
            ],
            // Options are required for select, checkbox, radio
            'options' => [
                function ($attribute, $value, $fail) use ($request) {
                    $type = $request->input('type');
                    if (in_array($type, ['select', 'checkbox', 'radio'])) {
                        if (empty($value)) {
                            $fail('Les options sont requises pour ce type de champ.');
                        }
                    }
                },
                'nullable',
                'string',
            ],
            'required' => ['boolean'],
            'active' => ['boolean'],
        ];
    }
}