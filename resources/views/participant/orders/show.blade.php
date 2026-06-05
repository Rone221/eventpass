<x-site-layout :title="'Commande '.$order->reference">
    <a href="{{ route('orders.index') }}" class="text-sm text-ink-400 hover:text-brand-600">← Mes commandes</a>

    @php $badge = ['pending' => 'bg-amber-100 text-amber-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-brand-100 text-brand-700', 'cancelled' => 'bg-ink-100 text-ink-600'][$order->status->value]; @endphp

    <div class="flex items-center justify-between mt-2 mb-6">
        <div>
            <h1 class="font-display font-extrabold text-3xl text-ink-900">{{ $order->event->title }}</h1>
            <p class="text-ink-400">Commande {{ $order->reference }}</p>
        </div>
        <span class="badge {{ $badge }} text-sm px-3 py-1.5">{{ $order->status->label() }}</span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card-pad">
            <h2 class="font-display font-bold text-lg text-ink-900 mb-4">Récapitulatif</h2>
            <div class="divide-y divide-ink-100">
                @foreach ($order->items as $item)
                    <div class="flex justify-between py-2.5 text-sm">
                        <span class="text-ink-700">{{ $item->ticketType->name }} <span class="text-ink-400">× {{ $item->quantity }}</span></span>
                        <span class="font-semibold text-ink-900">{{ number_format($item->subtotal(), 0, ',', ' ') }} F</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-3 font-display font-extrabold text-ink-900">
                    <span>Total</span><span>{{ number_format($order->total_amount, 0, ',', ' ') }} F</span>
                </div>
            </div>

            @if ($order->isPaid() && $order->tickets->isNotEmpty())
                <div class="mt-6 pt-5 border-t border-ink-100">
                    <h3 class="font-display font-bold text-ink-900 mb-3">Vos billets</h3>
                    <div class="space-y-2">
                        @foreach ($order->tickets as $ticket)
                            <div class="flex items-center justify-between rounded-xl bg-ink-50 px-4 py-3 text-sm">
                                <span class="font-medium text-ink-800">{{ $ticket->ticketType->name }} — {{ $ticket->holder_name }}</span>
                                <span class="badge {{ $ticket->isScanned() ? 'bg-ink-200 text-ink-600' : 'bg-emerald-100 text-emerald-700' }}">{{ $ticket->status->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('orders.tickets', $order) }}" class="btn-brand mt-5"><x-icon name="download" class="w-5 h-5"/> Télécharger les billets (PDF)</a>
                </div>
            @endif
        </div>

        <div class="card-pad h-fit">
            <h2 class="font-display font-bold text-lg text-ink-900 mb-3">Paiement</h2>
            @if ($order->isPaid())
                <div class="flex items-center gap-2 text-emerald-700 font-medium"><x-icon name="check-circle" class="w-5 h-5"/> Confirmé</div>
                <p class="text-sm text-ink-500 mt-2">Le {{ $order->paid_at->translatedFormat('j M Y à H\hi') }}, via le webhook {{ ucfirst($order->payment_provider) }}.</p>
            @elseif ($order->isPending())
                <div class="flex items-center gap-2 text-amber-700 font-medium"><x-icon name="clock" class="w-5 h-5"/> En attente</div>
                @if (config('paydunya.fake'))
                    <a href="{{ route('payment.fake.show', $order) }}" class="btn-brand btn-sm w-full mt-4">Reprendre le paiement</a>
                @endif
            @else
                <div class="flex items-center gap-2 text-brand-700 font-medium"><x-icon name="x-circle" class="w-5 h-5"/> {{ $order->status->label() }}</div>
            @endif
        </div>
    </div>
</x-site-layout>
