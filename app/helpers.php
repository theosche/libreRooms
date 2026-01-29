<?php

use Illuminate\Support\Number;
use App\Models\Owner;

if (!function_exists('currency')) {
    /**
     * Format a number as currency
     */
    function currency(float|int $amount, ?Owner $owner, ?string $currency = null, ?string $locale = null): string
    {
        return Number::currency(
            $amount,
            $currency ?? $owner->getCurrency(),
            $locale ?? $owner->getLocale(),
        );
    }
}
