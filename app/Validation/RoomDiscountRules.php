<?php

namespace App\Validation;

use Illuminate\Validation\Rule;
use App\Enums\DiscountTypes;
use App\Enums\ContactTypes;

class RoomDiscountRules
{
    public static function rules($discountId = null): array
    {
        $user = auth()->user();
        $userId = $user->id;

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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => [
                'required',
                Rule::in(array_map(fn ($case) => $case->value, DiscountTypes::cases())),
            ],
            'limit_to_contact_type' => [
                'nullable',
                Rule::in(array_map(fn ($case) => $case->value, ContactTypes::cases())),
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'active' => ['boolean'],
        ];
    }
}