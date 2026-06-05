<x-site-layout title="Retour de paiement">
    @php $ticketsReady = $order->isPaid() && $order->tickets()->exists(); @endphp
    @if ($order->isPaid() && ! $ticketsReady)
        <meta http-equiv="refresh" content="3">
    @endif

    <div class="max-w-lg mx-auto card-pad text-center py-10">
        @if ($order->isPaid())
            <div class="grid place-items-center w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 mx-auto"><x-icon name="check-circle" class="w-10 h-10"/></div>
            <h1 class="font-display font-extrabold text-2xl text-ink-900 mt-5">Paiement confirmé !</h1>
            <p class="text-ink-500 mt-1">Commande {{ $order->reference }} — {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</p>

            @if ($ticketsReady)
                <p class="text-sm text-ink-600 mt-4">Vos billets ont été générés et envoyés par email.</p>
                <a href="{{ route('orders.tickets', $order) }}" class="btn-brand mt-5"><x-icon name="download" class="w-5 h-5"/> Télécharger les billets</a>
                <div class="mt-3"><a href="{{ route('orders.show', $order) }}" class="link text-sm">Voir ma commande</a></div>
            @else
                <div class="mt-5 inline-flex items-center gap-2 text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-4 py-2 text-sm">
                    <x-icon name="clock" class="w-4 h-4 animate-spin"/> Génération des billets en cours…
                </div>
            @endif
        @elseif ($order->isPending())
            <div class="grid place-items-center w-20 h-20 rounded-full bg-amber-100 text-amber-600 mx-auto"><x-icon name="clock" class="w-10 h-10"/></div>
            <h1 class="font-display font-extrabold text-2xl text-ink-900 mt-5">Paiement en attente</h1>
            <p class="text-ink-500 mt-2 text-sm">Nous attendons la confirmation (notification IPN). La commande sera validée automatiquement dès réception.</p>
            <a href="{{ route('orders.show', $order) }}" class="link mt-5 inline-block">Suivre ma commande</a>
        @else
            <div class="grid place-items-center w-20 h-20 rounded-full bg-brand-100 text-brand-600 mx-auto"><x-icon name="x-circle" class="w-10 h-10"/></div>
            <h1 class="font-display font-extrabold text-2xl text-ink-900 mt-5">Paiement {{ $order->status->label() }}</h1>
            <a href="{{ route('events.show', $order->event) }}" class="btn-outline mt-5">Réessayer</a>
        @endif
    </div>
</x-site-layout>
