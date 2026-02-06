<?php

namespace App\Enums;

enum CalendarViewModes: string
{
    case SLOT = 'slot';
    case TITLE = 'title';
    case FULL = 'full';

    public function label(): string
    {
        return match ($this) {
            self::SLOT => __('Slots only'),
            self::TITLE => __('Event title'),
            self::FULL => __('Full'),
        };
    }
}
