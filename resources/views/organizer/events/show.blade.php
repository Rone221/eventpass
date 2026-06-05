<x-site-layout :title="$event->title">
    <a href="{{ route('organizer.events.index') }}" class="text-sm text-ink-400 hover:text-brand-600">← Mes événements</a>

    <div class="flex flex-wrap items-start justify-between gap-4 mt-2 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-display font-extrabold text-3xl text-ink-900">{{ $event->title }}</h1>
                @php $badge = ['draft' => 'bg-ink-100 text-ink-600', 'published' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-brand-100 text-brand-700'][$event->status->value]; @endphp
                <span class="badge {{ $badge }}">{{ $event->status->label() }}</span>
            </div>
            <p class="text-ink-400 mt-1">{{ $event->starts_at->translatedFormat('l j F Y à H\hi') }} · {{ $event->venue }}, {{ $event->city }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('organizer.scanner') }}" class="btn-ink btn-sm"><x-icon name="qr-code" class="w-4 h-4"/> Scanner</a>
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn-outline btn-sm"><x-icon name="pencil" class="w-4 h-4"/> Modifier</a>
            <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Supprimer définitivement cet événement ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm border border-brand-200 text-brand-600 hover:bg-brand-50">Supprimer</button>
            </form>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $cards = [
                ['Billets vendus', $stats['tickets_sold'], 'ticket', 'bg-brand-50 text-brand-600'],
                ['Recette', number_format($stats['revenue'], 0, ',', ' ').' F', 'banknotes', 'bg-emerald-50 text-emerald-600'],
                ['Scannés', $stats['scanned'].' / '.$stats['total_tickets'], 'check-circle', 'bg-sky-50 text-sky-600'],
                ['Catégories', $event->ticketTypes->count(), 'tag', 'bg-violet-50 text-violet-600'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $icon, $tint])
            <div class="card-pad">
                <span class="grid place-items-center w-10 h-10 rounded-xl {{ $tint }}"><x-icon :name="$icon" class="w-5 h-5"/></span>
                <div class="mt-3 font-display font-extrabold text-2xl text-ink-900">{{ $value }}</div>
                <div class="text-xs text-ink-400">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="card-pad">
            <h2 class="font-display font-bold text-lg text-ink-900 mb-4">Catégories de billets</h2>
            <div class="space-y-3">
                @foreach ($event->ticketTypes as $type)
                    @php $pct = $type->quantity > 0 ? round($type->sold / $type->quantity * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-semibold text-ink-800">{{ $type->name }} · {{ number_format($type->price, 0, ',', ' ') }} F</span>
                            <span class="text-ink-400">{{ $type->sold }} / {{ $type->quantity }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                            <div class="h-full bg-brand-500 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-pad">
            <h2 class="font-display font-bold text-lg text-ink-900 mb-4">Dernières ventes</h2>
            @if ($event->orders->isEmpty())
                <p class="text-sm text-ink-400 py-6 text-center">Aucune vente pour le moment.</p>
            @else
                <ul class="divide-y divide-ink-100 text-sm">
                    @foreach ($event->orders->take(8) as $order)
                        <li class="py-2.5 flex justify-between items-center">
                            <span class="flex items-center gap-2">
                                <span class="grid place-items-center w-7 h-7 rounded-full bg-ink-100 text-ink-600 text-xs font-semibold">{{ strtoupper(substr($order->customer_name, 0, 1)) }}</span>
                                <span class="text-ink-700">{{ $order->customer_name }}</span>
                            </span>
                            <span class="font-semibold text-ink-900">{{ number_format($order->total_amount, 0, ',', ' ') }} F</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @unless ($event->isPublished())
        <div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm flex items-center gap-2">
            <x-icon name="warning" class="w-5 h-5 shrink-0"/> Cet événement est en « {{ $event->status->label() }} » : il n'apparaît pas dans le catalogue public.
        </div>
    @endunless
</x-site-layout>
