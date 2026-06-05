<?php

namespace App\Enums;

/**
 * Rôles utilisateurs (Module 1 du sujet).
 * Deux types : Organisateur (crée des événements & vend des billets)
 * et Participant (achète des billets).
 */
enum UserRole: string
{
    case Organizer = 'organizer';
    case Participant = 'participant';

    public function label(): string
    {
        return match ($this) {
            self::Organizer => 'Organisateur',
            self::Participant => 'Participant',
        };
    }
}
