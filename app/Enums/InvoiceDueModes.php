<?php

namespace App\Enums;

use function Symfony\Component\VarDumper\Dumper\esc;

enum InvoiceDueModes: string
{
    case BEFORE_EVENT = 'before_event';
    case AFTER_EVENT = 'after_event';
    case AFTER_CONFIRM = 'after_confirm';

    public function label(?int $days=null): string
    {
        return match ($this) {
            self::BEFORE_EVENT => "Facture due " . ($days ?? "X") . " jours avant la première date de réservation",
            self::AFTER_EVENT => "Facture due " . ($days ?? "X") . " jours après la première date de réservation",
            self::AFTER_CONFIRM => "Facture due " . ($days ?? "X") . " jours après la confirmation de réservation",
        };
    }
    public function shortLabel(?int $days=null): string
    {
        return match ($this) {
            self::BEFORE_EVENT => ($days ?? "X") . "j avant év.",
            self::AFTER_EVENT => ($days ?? "X") . "j après év.",
            self::AFTER_CONFIRM => ($days ?? "X") . "j",
        };
    }
}
