<?php

namespace App\DTO;
use Carbon\Carbon;

class EventDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $status,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly ?string $uid,
        public readonly Carbon $created,
        public readonly Carbon $updated,
        public readonly ?string $location
    ) {}
}
