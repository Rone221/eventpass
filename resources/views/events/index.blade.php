<x-site-layout title="Événements" :wide="true">

    @if ($q === '')
        {{-- ───── HERO ───── --}}
        <section class="relative overflow-hidden bg-ink-900">
            <div class="absolute inset-0 opacity-30"
                 style="background-image: radial-gradient(circle at 15% 20%, #F05537 0, transparent 40%), radial-gradient(circle at 85% 80%, #7C3AED 0, transparent 45%);"></div>
            <div class="container-app relative py-16 sm:py-24">
                <div class="max-w-2xl">
                    <span class="chip bg-white/10 text-white backdrop-blur"><x-icon name="sparkles" class="w-4 h-4"/> Vivez des expériences inoubliables</span>
                    <h1 class="mt-5 font-display font-extrabold text-4xl sm:text-5xl text-white leading-tight">
                        Trouvez votre prochain <span class="text-brand-400">événement</span>.
                    </h1>
                    <p class="mt-4 text-lg text-ink-200">
                        Conférences, concerts, festivals… Réservez vos billets en quelques clics et payez via Wave, Orange Money ou carte bancaire.
                    </p>

                    <form action="{{ route('events.index') }}" method="GET" class="mt-8 flex gap-2 bg-white rounded-full p-1.5 shadow-pop max-w-xl">
                        <div class="relative flex-1">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-ink-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="search" name="q" placeholder="Rechercher un événement, une ville…"
                                   class="w-full border-0 bg-transparent pl-12 pr-3 py-3 text-ink-900 focus:ring-0 placeholder:text-ink-300">
                        </div>
                        <button class="btn-brand">Rechercher</button>
                    </form>
                </div>
            </div>
        </section>
    @endif

    {{-- ───── LISTE ───── --}}
    <section class="container-app py-10">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="font-display font-bold text-2xl text-ink-900">
                    {{ $q !== '' ? 'Résultats pour « '.$q.' »' : 'Événements à venir' }}
                </h2>
                <p class="text-ink-400 text-sm mt-1">{{ $events->count() }} événement(s)</p>
            </div>
            @if ($q !== '')
                <a href="{{ route('events.index') }}" class="link text-sm">Réinitialiser</a>
            @endif
        </div>

        @if ($events->isEmpty())
            <div class="card-pad text-center py-16 text-ink-400">
                <x-icon name="search" class="w-10 h-10 mx-auto mb-3 text-ink-300"/>
                Aucun événement trouvé{{ $q !== '' ? ' pour cette recherche' : '' }}.
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($events as $event)
                    @php $minPrice = $event->ticketTypes->min('price'); @endphp
                    <a href="{{ route('events.show', $event) }}"
                       class="group card overflow-hidden hover:shadow-card-hover hover:-translate-y-1 transition duration-200">
                        <x-event-cover :event="$event" class="h-40">
                            <div class="absolute top-3 left-3 bg-white rounded-xl px-3 py-1.5 text-center shadow-md">
                                <div class="text-brand-600 text-[10px] font-extrabold uppercase leading-none">{{ $event->starts_at->translatedFormat('M') }}</div>
                                <div class="text-ink-900 font-extrabold text-lg leading-none mt-0.5">{{ $event->starts_at->format('d') }}</div>
                            </div>
                        </x-event-cover>
                        <div class="p-5">
                            <h3 class="font-display font-bold text-ink-900 text-lg leading-snug line-clamp-2 group-hover:text-brand-600 transition">{{ $event->title }}</h3>
                            <div class="mt-2 space-y-1.5 text-sm text-ink-500">
                                <div class="flex items-center gap-2"><x-icon name="clock" class="w-4 h-4 text-ink-300 shrink-0"/> {{ $event->starts_at->translatedFormat('D j M · H\hi') }}</div>
                                <div class="flex items-center gap-2"><x-icon name="map-pin" class="w-4 h-4 text-ink-300 shrink-0"/> {{ $event->venue }}, {{ $event->city }}</div>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-display font-bold text-ink-900">
                                    @if (is_null($minPrice)) <span class="text-ink-400 font-medium text-sm">Bientôt</span>
                                    @elseif ($minPrice == 0) Gratuit
                                    @else <span class="text-ink-400 text-xs font-medium">dès</span> {{ number_format($minPrice, 0, ',', ' ') }} F
                                    @endif
                                </span>
                                <span class="chip-brand text-xs">Voir le détail</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-site-layout>
