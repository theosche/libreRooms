<?php

namespace App\Support;

use Carbon\Carbon;

class DateHelper
{
    public static function fromLocalInput(string $value, string $timezone): Carbon
    {
        return Carbon::createFromFormat(
            '!Y-m-d\TH:i',
            $value,
            new \DateTimeZone($timezone)
        )->setTimezone(new \DateTimeZone('UTC'));
    }
    public static function fromFullCalendar(string $value, string $timezone): Carbon
    {
        return Carbon::createFromFormat(
            '!Y-m-d',
            $value,
            new \DateTimeZone($timezone)
        )->setTimezone(new \DateTimeZone('UTC'));
    }
}
