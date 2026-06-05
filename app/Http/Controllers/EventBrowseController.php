<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Http\Request;

/**
 * Catalogue public des événements (consultable sans connexion).
 */
class EventBrowseController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $events = Event::where('status', EventStatus::Published)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('venue', 'like', "%{$q}%");
                });
            })
            ->with('ticketTypes')
            ->orderBy('starts_at')
            ->get();

        return view('events.index', ['events' => $events, 'q' => $q]);
    }

    public function show(Event $event)
    {
        abort_unless($event->isPublished(), 404);

        $event->load('ticketTypes', 'organizer');

        return view('events.show', compact('event'));
    }
}
