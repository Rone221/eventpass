<?php

namespace App\Enums;

/**
 * Statut d'un billet électronique.
 * Le passage Available -> Scanned se fait via l'API de scan (Sanctum),
 * de façon atomique pour empêcher tout double-scan.
 */
enum TicketStatus: string
{
    case Available = 'available';
    case Scanned = 'scanned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Scanned => 'Scanné',
            self::Cancelled => 'Annulé',
        };
    }
}
