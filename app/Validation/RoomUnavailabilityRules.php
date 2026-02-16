<?php

namespace App\Validation;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;

class RoomUnavailabilityRules
{
    public static function rules(): array
    {
        $user = auth()->user();

        // Get room IDs that the user can moderate
        $roomIds = $user->getAccessibleRoomIds(UserRole::MODERATOR);
        $roomRule = Rule::exists('rooms', 'id')->whereIn('id', $roomIds);

        return [
            'room_id' => [
                'required',
                'integer',
                $roomRule,
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ];
    }
}
