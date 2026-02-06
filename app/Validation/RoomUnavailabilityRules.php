<?php

namespace App\Validation;

use Illuminate\Validation\Rule;

class RoomUnavailabilityRules
{
    public static function rules(): array
    {
        $user = auth()->user();

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
            'title' => ['nullable', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ];
    }
}
