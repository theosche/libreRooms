<?php

namespace App\Enums;

enum DiscountTypes: string
{
    case FIXED = 'fixed';
    case PERCENT = 'percent';
}
