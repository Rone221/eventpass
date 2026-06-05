<x-site-layout title="Paiement annulé">
    <div class="max-w-lg mx-auto card-pad text-center py-10">
        <div class="grid place-items-center w-20 h-20 rounded-full bg-ink-100 text-ink-500 mx-auto"><x-icon name="x-circle" class="w-10 h-10"/></div>
        <h1 class="font-display font-extrabold text-2xl text-ink-900 mt-5">Paiement annulé</h1>
        <p class="text-ink-500 mt-2 text-sm">Votre commande {{ $order->reference }} n'a pas été réglée.</p>
        <a href="{{ route('events.show', $order->event) }}" class="btn-brand mt-5">Retour à l'événement</a>
    </div>
</x-site-layout>
