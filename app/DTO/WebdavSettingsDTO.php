<?php

namespace App\DTO;

class WebdavSettingsDTO
{
    public function __construct(
        public readonly ?string $user,
        public readonly ?string $pass,
        public readonly ?string $webdavUrl,
        public readonly ?string $savePath,
    ) {}
    public function valid(): bool
    {
        return $this->user && $this->pass && $this->webdavUrl && $this->savePath;
    }
}
