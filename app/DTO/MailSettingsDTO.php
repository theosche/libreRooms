<?php

namespace App\DTO;

class MailSettingsDTO
{
    public function __construct(
        public readonly ?string $host,
        public readonly ?int $port,
        public readonly ?string $user,
        public readonly ?string $pass,
    ) {}
    public function valid(): bool
    {
        return $this->host && $this->port && $this->user && $this->pass;
    }
}
