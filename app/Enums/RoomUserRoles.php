<?php

namespace App\Enums;

enum RoomUserRoles: string
{
    case VIEWER = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::VIEWER => 'Lecteur',
        };
    }
}
