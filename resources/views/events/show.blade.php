<x-site-layout :title="$event->title" :wide="true">

    {{-- Bannière --}}
    <x-event-cover :event="$event" class="h-56 sm:h-72">
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/70 to-transparent"></div>
        <div class="container-app absolute inset-x-0 bottom-0 pb-6">
            <a href="{{ route('events.index') }}" class="text-white/80 text-sm hover:text-white">← Tous les événements</a>
            <h1 class="mt-2 font-display font-extrabold text-3xl sm:text-4xl text-white max-w-3xl drop-shadow">{{ $event->title }}</h1>
        </div>
    </x-event-cover>

    <div class="container-app py-10 grid lg:grid-cols-3 gap-8">
        {{-- Détails --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="flex flex-wrap gap-3">
                <span class="chip-soft"><x-icon name="calendar" class="w-4 h-4 text-ink-400"/> {{ $event->starts_at->translatedFormat('l j F Y') }}</span>
                <span class="chip-soft"><x-icon name="clock" class="w-4 h-4 text-ink-400"/> {{ $event->starts_at->translatedFormat('H\hi') }}</span>
                <span class="chip-soft"><x-icon name="map-pin" class="w-4 h-4 text-ink-400"/> {{ $event->city }}</span>
            </div>

            @if ($event->description)
                <div>
                    <h2 class="font-display font-bold text-xl text-ink-900 mb-3">À propos</h2>
                    <p class="text-ink-600 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
                </div>
            @endif

            <div>
                <h2 class="font-display font-bold text-xl text-ink-900 mb-3">Lieu</h2>
                <div class="card-pad flex items-start gap-4">
                    <span class="grid place-items-center w-12 h-12 rounded-xl bg-brand-50 text-brand-500 shrink-0"><x-icon name="building" class="w-6 h-6"/></span>
                    <div>
                        <div class="font-semibold text-ink-900">{{ $event->venue }}</div>
                        <div class="text-ink-500 text-sm">{{ $event->city }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <span class="grid place-items-center w-11 h-11 rounded-full bg-ink-900 text-white font-semibold">{{ strtoupper(substr($event->organizer->name, 0, 1)) }}</span>
                <div>
                    <div class="text-xs text-ink-400">Organisé par</div>
                    <div class="font-semibold text-ink-900">{{ $event->organizer->name }}</div>
                </div>
            </div>
        </div>

        {{-- Panneau d'achat --}}
        <div class="lg:col-span-1">
            <div class="card sticky top-24 overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100">
                    <h2 class="font-display font-bold text-lg text-ink-900">Billets</h2>
                </div>

                @if ($event->ticketTypes->isEmpty())
                    <p class="px-6 py-8 text-sm text-ink-400 text-center">Aucun billet en vente pour le moment.</p>
                @elseif (auth()->check() && auth()->user()->isOrganizer())
                    <p class="px-6 py-8 text-sm text-ink-400 text-center">Vous êtes connecté en organisateur. La billetterie est réservée aux participants.</p>
                @else
                    <form method="POST" action="{{ route('checkout.store', $event) }}" x-data="checkout()">
                        @csrf
                        <div class="p-6 space-y-3">
                            @foreach ($event->ticketTypes as $type)
                                <div class="rounded-xl border border-ink-100 p-4 {{ $type->isSoldOut() ? 'opacity-60' : '' }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-ink-900">{{ $type->name }}</div>
                                            <div class="text-brand-600 font-display font-bold">{{ $type->price == 0 ? 'Gratuit' : number_format($type->price, 0, ',', ' ').' F' }}</div>
                                            <div class="text-xs text-ink-400 mt-0.5">{{ $type->isSoldOut() ? 'Épuisé' : $type->remaining().' restant(s)' }}</div>
                                        </div>
                                        @unless ($type->isSoldOut())
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button" @click="dec('{{ $type->id }}')" class="w-8 h-8 rounded-full border border-ink-200 text-ink-600 hover:border-brand-400 hover:text-brand-600 grid place-items-center"><x-icon name="minus" class="w-4 h-4"/></button>
                                                <input type="number" name="quantities[{{ $type->id }}]" x-model.number="qty['{{ $type->id }}']"
                                                       min="0" max="{{ min(10, $type->remaining()) }}" readonly
                                                       class="w-12 text-center border-0 bg-transparent font-semibold text-ink-900 focus:ring-0 p-0">
                                                <button type="button" @click="inc('{{ $type->id }}', {{ min(10, $type->remaining()) }})" class="w-8 h-8 rounded-full border border-ink-200 text-ink-600 hover:border-brand-400 hover:text-brand-600 grid place-items-center"><x-icon name="plus" class="w-4 h-4"/></button>
                                            </div>
                                        @endunless
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-6 pb-6 pt-2 border-t border-ink-100">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-ink-500">Total</span>
                                <span class="font-display font-extrabold text-xl text-ink-900" x-text="fmt(total())">0 F</span>
                            </div>
                            @guest
                                <a href="{{ route('login') }}" class="btn-brand w-full">Connectez-vous pour acheter</a>
                            @else
                                <button type="submit" class="btn-brand w-full" :disabled="total() === 0" :class="total() === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    Payer
                                </button>
                                <div class="flex items-center justify-center gap-1.5 text-xs text-ink-400 mt-3">
                                    <x-icon name="wallet" class="w-4 h-4"/> Wave · Orange Money · Free Money
                                    <x-icon name="credit-card" class="w-4 h-4 ml-1"/> CB
                                </div>
                            @endguest
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        function checkout() {
            return {
                qty: {},
                prices: @js($event->ticketTypes->pluck('price', 'id')),
                inc(id, max) { this.qty[id] = Math.min((this.qty[id] || 0) + 1, max); },
                dec(id) { this.qty[id] = Math.max((this.qty[id] || 0) - 1, 0); },
                total() {
                    let t = 0;
                    for (const id in this.qty) t += (this.qty[id] || 0) * (this.prices[id] || 0);
                    return t;
                },
                fmt(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' F'; },
            };
        }
    </script>
</x-site-layout>
