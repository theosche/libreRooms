<?php

namespace App\Enums;

enum RoomCurrentStatus: string
{
    case FREE = 'free';
    case OCCUPIED = 'occupied';
    case UNAVAILABLE = 'unavailable';
    case OUTSIDE_HOURS = 'outside_hours';

    public function label(): string
    {
        return match ($this) {
            self::FREE => __('Free'),
            self::OCCUPIED => __('Occupied'),
            self::UNAVAILABLE => __('Unavailable'),
            self::OUTSIDE_HOURS => __('Outside bookable hours'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FREE => 'green',
            self::OCCUPIED => 'red',
            self::UNAVAILABLE => 'orange',
            self::OUTSIDE_HOURS => 'gray',
        };
    }
}
