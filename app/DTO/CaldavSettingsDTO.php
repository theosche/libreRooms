<?php

namespace App\DTO;

class CaldavSettingsDTO
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $user,
        public readonly ?string $pass,
    ) {}
    public function valid(): bool
    {
        return $this->url && $this->user && $this->pass;
    }
}
