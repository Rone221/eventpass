<x-site-layout title="Guichet de paiement (simulation)">
    <div class="max-w-md mx-auto">
        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-2 mb-4 flex items-center gap-2">
            <x-icon name="beaker" class="w-4 h-4 shrink-0"/> <span>Mode simulateur (<code>PAYMENT_FAKE_GATEWAY=true</code>) — aucun paiement réel. Désactivez-le pour utiliser le vrai sandbox Paydunya.</span>
        </div>

        <div class="card overflow-hidden">
            <div class="bg-ink-900 p-6 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 80% 20%, #F05537 0, transparent 50%);"></div>
                <div class="relative">
                    <div class="text-sm text-ink-300">Montant à payer</div>
                    <div class="font-display font-extrabold text-3xl mt-1">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</div>
                    <div class="text-xs text-ink-300 mt-1">{{ $order->event->title }} · {{ $order->reference }}</div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-4 gap-2 mb-6 text-center text-xs text-ink-500">
                    <div class="rounded-xl border border-ink-100 py-3 flex flex-col items-center gap-1"><x-icon name="phone" class="w-5 h-5 text-ink-400"/>Wave</div>
                    <div class="rounded-xl border border-ink-100 py-3 flex flex-col items-center gap-1"><x-icon name="phone" class="w-5 h-5 text-ink-400"/>Orange</div>
                    <div class="rounded-xl border border-ink-100 py-3 flex flex-col items-center gap-1"><x-icon name="phone" class="w-5 h-5 text-ink-400"/>Free</div>
                    <div class="rounded-xl border border-ink-100 py-3 flex flex-col items-center gap-1"><x-icon name="credit-card" class="w-5 h-5 text-ink-400"/>CB</div>
                </div>

                <form method="POST" action="{{ route('payment.fake.pay', $order) }}" class="space-y-3">
                    @csrf
                    <button name="outcome" value="success" class="btn w-full bg-emerald-600 text-white py-3 hover:bg-emerald-700"><x-icon name="check-circle" class="w-5 h-5"/> Simuler un paiement réussi</button>
                    <button name="outcome" value="fail" class="btn w-full bg-brand-50 text-brand-700 py-3 hover:bg-brand-100"><x-icon name="x-circle" class="w-5 h-5"/> Simuler un échec</button>
                </form>

                <p class="text-xs text-ink-400 mt-4 text-center">« Paiement réussi » déclenche une notification IPN signée, traitée par le même webhook que Paydunya.</p>
            </div>
        </div>
    </div>
</x-site-layout>
