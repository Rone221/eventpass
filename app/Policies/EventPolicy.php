<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /** Seul l'organisateur propriétaire peut gérer son événement. */
    public function manage(User $user, Event $event): bool
    {
        return $user->isOrganizer() && $user->id === $event->organizer_id;
    }
}
