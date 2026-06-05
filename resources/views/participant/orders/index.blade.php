<x-site-layout title="Mes commandes">
    <h1 class="font-display font-extrabold text-3xl text-ink-900 mb-8">Mes commandes</h1>

    @if ($orders->isEmpty())
        <div class="card-pad text-center py-16">
            <x-icon name="ticket" class="w-12 h-12 mx-auto mb-3 text-ink-300"/>
            <p class="text-ink-500">Aucune commande pour l'instant.</p>
            <a href="{{ route('events.index') }}" class="btn-brand mt-5">Découvrir les événements</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                @php $badge = ['pending' => 'bg-amber-100 text-amber-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-brand-100 text-brand-700', 'cancelled' => 'bg-ink-100 text-ink-600'][$order->status->value]; @endphp
                <a href="{{ route('orders.show', $order) }}" class="card flex items-center gap-4 p-4 hover:shadow-card-hover transition">
                    <x-event-cover :event="$order->event" class="w-16 h-16 rounded-xl shrink-0" />
                    <div class="flex-1 min-w-0">
                        <div class="font-display font-bold text-ink-900 truncate">{{ $order->event->title }}</div>
                        <div class="text-sm text-ink-400">{{ $order->reference }} · {{ $order->created_at->translatedFormat('j M Y') }} · {{ $order->tickets_count }} billet(s)</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-display font-bold text-ink-900">{{ number_format($order->total_amount, 0, ',', ' ') }} F</div>
                        <span class="badge {{ $badge }} mt-1">{{ $order->status->label() }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-site-layout>
