<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case FINISHED = 'finished';

    public function icalStatus(): string
    {
        return match ($this) {
            self::PENDING => "PENDING",
            self::CONFIRMED, self::FINISHED => "CONFIRMED",
            self::CANCELLED => "CANCELLED",
        };
    }
    public function label(): string
    {
        return match ($this) {
            self::PENDING => "À confirmer",
            self::CONFIRMED => "Confirmé",
            self::FINISHED => "Terminé",
            self::CANCELLED => "Annulé",
        };
    }
}
