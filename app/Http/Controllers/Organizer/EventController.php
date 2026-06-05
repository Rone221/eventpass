<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /** Liste des événements de l'organisateur connecté. */
    public function index(Request $request)
    {
        $events = $request->user()->events()
            ->withCount('tickets')
            ->latest()
            ->get();

        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        return view('organizer.events.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);

        $event = $request->user()->events()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'venue' => $data['venue'],
            'city' => $data['city'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ]);

        $this->syncTicketTypes($event, $data['ticket_types']);
        $this->handleImage($request, $event);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event)
    {
        Gate::authorize('manage', $event);

        $event->load(['ticketTypes', 'orders' => fn ($q) => $q->where('status', 'paid')->latest()]);

        $stats = [
            'tickets_sold' => $event->ticketsSoldCount(),
            'revenue' => $event->orders()->where('status', 'paid')->sum('total_amount'),
            'scanned' => $event->tickets()->where('status', 'scanned')->count(),
            'total_tickets' => $event->tickets()->count(),
        ];

        return view('organizer.events.show', compact('event', 'stats'));
    }

    public function edit(Event $event)
    {
        Gate::authorize('manage', $event);
        $event->load('ticketTypes');

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        Gate::authorize('manage', $event);

        $data = $this->validateEvent($request);

        $event->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'venue' => $data['venue'],
            'city' => $data['city'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ]);

        $this->syncTicketTypes($event, $data['ticket_types']);
        $this->handleImage($request, $event);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        Gate::authorize('manage', $event);
        $this->deleteImageIfLocal($event->image_path);
        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Événement supprimé.');
    }

    /* ───────────────── Helpers ───────────────── */

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:draft,published,cancelled'],

            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],

            'ticket_types' => ['required', 'array', 'min:1'],
            'ticket_types.*.id' => ['nullable', 'integer'],
            'ticket_types.*.name' => ['required', 'string', 'max:120'],
            'ticket_types.*.price' => ['required', 'integer', 'min:0'],
            'ticket_types.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
    }

    /**
     * Gère l'upload de l'image de couverture : suppression demandée et/ou
     * remplacement par un nouveau fichier (stocké sur le disque "public").
     */
    private function handleImage(Request $request, Event $event): void
    {
        if ($request->boolean('remove_image') && $event->image_path) {
            $this->deleteImageIfLocal($event->image_path);
            $event->update(['image_path' => null]);
        }

        if ($request->hasFile('image')) {
            $this->deleteImageIfLocal($event->image_path);   // remplace l'ancienne
            $path = $request->file('image')->store('events', 'public');
            $event->update(['image_path' => $path]);
        }
    }

    /** Supprime un fichier local (ignore les URLs distantes comme les photos de démo). */
    private function deleteImageIfLocal(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Synchronise les catégories de billets : met à jour celles qui existent,
     * crée les nouvelles. On ne supprime pas une catégorie déjà vendue
     * (sold > 0) pour préserver l'intégrité des commandes passées.
     */
    private function syncTicketTypes(Event $event, array $types): void
    {
        $keptIds = [];

        foreach ($types as $type) {
            if (! empty($type['id'])) {
                $model = $event->ticketTypes()->whereKey($type['id'])->first();
                if ($model) {
                    // La capacité ne peut pas descendre sous le nombre déjà vendu
                    $quantity = max((int) $type['quantity'], $model->sold);
                    $model->update([
                        'name' => $type['name'],
                        'price' => $type['price'],
                        'quantity' => $quantity,
                    ]);
                    $keptIds[] = $model->id;

                    continue;
                }
            }

            $model = $event->ticketTypes()->create([
                'name' => $type['name'],
                'price' => $type['price'],
                'quantity' => $type['quantity'],
            ]);
            $keptIds[] = $model->id;
        }

        // Suppression des catégories retirées du formulaire ET jamais vendues
        $event->ticketTypes()
            ->whereNotIn('id', $keptIds)
            ->where('sold', 0)
            ->delete();
    }
}
