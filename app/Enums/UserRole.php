<?php

namespace App\Enums;

enum UserRole: string
{
    case VIEWER = 'viewer';
    case MODERATOR = 'moderator';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::VIEWER => __('Viewer'),
            self::MODERATOR => __('Moderator'),
            self::ADMIN => __('Administrator'),
        };
    }

    public function label_short(): string
    {
        return match ($this) {
            self::VIEWER => __('Viewer.short'),
            self::MODERATOR => __('Moderator.short'),
            self::ADMIN => __('Administrator.short'),
        };
    }

    /**
     * Check if this role has at least the given permission level.
     */
    public function hasAtLeast(self $role): bool
    {
        return $this->weight() >= $role->weight();
    }

    /**
     * Get the weight for permission comparison.
     */
    private function weight(): int
    {
        return match ($this) {
            self::VIEWER => 1,
            self::MODERATOR => 2,
            self::ADMIN => 3,
        };
    }
}
