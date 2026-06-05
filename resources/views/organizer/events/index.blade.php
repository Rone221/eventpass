<x-site-layout title="Mes événements">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-display font-extrabold text-3xl text-ink-900">Mes événements</h1>
            <p class="text-ink-400 mt-1">Gérez vos événements, vos ventes et vos billets.</p>
        </div>
        <a href="{{ route('organizer.events.create') }}" class="btn-brand">+ Créer un événement</a>
    </div>

    @if ($events->isEmpty())
        <div class="card-pad text-center py-16">
            <x-icon name="megaphone" class="w-12 h-12 mx-auto mb-3 text-ink-300"/>
            <p class="text-ink-500">Vous n'avez pas encore créé d'événement.</p>
            <a href="{{ route('organizer.events.create') }}" class="btn-brand mt-5">Créer mon premier événement</a>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                @php
                    $badge = ['draft' => 'bg-ink-100 text-ink-600', 'published' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-brand-100 text-brand-700'][$event->status->value];
                @endphp
                <a href="{{ route('organizer.events.show', $event) }}" class="group card overflow-hidden hover:shadow-card-hover hover:-translate-y-1 transition">
                    <x-event-cover :event="$event" class="h-28">
                        <span class="absolute top-3 right-3 badge {{ $badge }}">{{ $event->status->label() }}</span>
                    </x-event-cover>
                    <div class="p-5">
                        <h3 class="font-display font-bold text-ink-900 group-hover:text-brand-600 line-clamp-1">{{ $event->title }}</h3>
                        <p class="text-sm text-ink-400 mt-1">{{ $event->starts_at->translatedFormat('j M Y') }} · {{ $event->city }}</p>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-ink-500 flex items-center gap-1.5"><x-icon name="ticket" class="w-4 h-4 text-ink-300"/> {{ $event->tickets_count }} billet(s)</span>
                            <span class="link">Gérer</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-site-layout>
