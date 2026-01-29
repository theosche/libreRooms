<?php

namespace App\Enums;

enum EmbedCalendarModes: string
{
    case DISABLED = 'disabled';
    case ENABLED = 'enabled';
    case ADMIN_ONLY = 'admin_only';
}
