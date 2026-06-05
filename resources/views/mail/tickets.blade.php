<x-mail::message>
# Vos billets sont prêts

Bonjour {{ $order->customer_name }},

Votre paiement pour **{{ $event->title }}** a bien été confirmé. Merci !

**Récapitulatif**
- Référence : {{ $order->reference }}
- Date : {{ $event->starts_at->translatedFormat('l j F Y à H\hi') }}
- Lieu : {{ $event->venue }}, {{ $event->city }}
- Montant : {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA

Vous trouverez vos billets (avec QR code) en pièce jointe de cet email.
Présentez-les à l'entrée de l'événement.

<x-mail::button :url="route('orders.show', $order)">
Voir ma commande
</x-mail::button>

Merci,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
