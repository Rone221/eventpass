<?php

namespace App\Enums;

/**
 * Cycle de vie d'une commande.
 * Une commande n'est validée (Paid) QUE par le webhook IPN de Paydunya,
 * jamais sur la seule page de retour (return_url).
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente de paiement',
            self::Paid => 'Payée',
            self::Failed => 'Échouée',
            self::Cancelled => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Paid => 'emerald',
            self::Failed => 'red',
            self::Cancelled => 'gray',
        };
    }
}
