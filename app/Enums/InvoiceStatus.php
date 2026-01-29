<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DUE = 'due';
    case LATE = 'late';
    case PAID = 'paid';
    case TOO_LATE = 'too_late';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DUE => 'À payer',
            self::LATE => 'En retard',
            self::PAID => 'Payée',
            self::TOO_LATE => 'Impayée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DUE => 'bg-blue-100 text-blue-800',
            self::LATE => 'bg-yellow-100 text-yellow-800',
            self::PAID => 'bg-green-100 text-green-800',
            self::TOO_LATE => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-gray-100 text-gray-800',
        };
    }
}
