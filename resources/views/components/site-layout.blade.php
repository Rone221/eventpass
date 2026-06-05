@props(['title' => null, 'wide' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'EventPass') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink-700 font-sans antialiased flex flex-col">

    {{-- ───────────────────────── HEADER ───────────────────────── --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-ink-100" x-data="{ open: false }">
        <div class="container-app">
            <div class="flex items-center gap-4 h-16">
                {{-- Logo --}}
                <a href="{{ route('events.index') }}" class="flex items-center gap-2 shrink-0">
                    <span class="grid place-items-center w-9 h-9 rounded-xl bg-brand-500 text-white shadow-sm"><x-icon name="ticket" class="w-5 h-5"/></span>
                    <span class="font-display font-extrabold text-xl text-ink-900 tracking-tight hidden sm:block">Event<span class="text-brand-500">Pass</span></span>
                </a>

                {{-- Recherche --}}
                <form action="{{ route('events.index') }}" method="GET" class="flex-1 max-w-md hidden md:block">
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-ink-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un événement, une ville…"
                               class="w-full rounded-full border-ink-200 bg-ink-50 pl-11 pr-4 py-2.5 text-sm focus:bg-white">
                    </div>
                </form>

                <div class="flex items-center gap-1 ml-auto">
                    <a href="{{ route('events.index') }}" class="hidden sm:inline-flex px-3 py-2 rounded-lg text-sm font-medium text-ink-600 hover:text-brand-600 hover:bg-brand-50">Explorer</a>

                    @auth
                        @if(auth()->user()->isOrganizer())
                            <a href="{{ route('organizer.events.index') }}" class="hidden sm:inline-flex px-3 py-2 rounded-lg text-sm font-medium text-ink-600 hover:text-brand-600 hover:bg-brand-50">Mes événements</a>
                            <a href="{{ route('organizer.scanner') }}" class="hidden sm:inline-flex px-3 py-2 rounded-lg text-sm font-medium text-ink-600 hover:text-brand-600 hover:bg-brand-50">Scanner</a>
                        @else
                            <a href="{{ route('orders.index') }}" class="hidden sm:inline-flex px-3 py-2 rounded-lg text-sm font-medium text-ink-600 hover:text-brand-600 hover:bg-brand-50">Mes billets</a>
                        @endif

                        {{-- Menu utilisateur --}}
                        <div class="relative" x-data="{ menu: false }">
                            <button @click="menu = !menu" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-ink-50">
                                <span class="grid place-items-center w-8 h-8 rounded-full bg-ink-900 text-white text-sm font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <svg class="w-4 h-4 text-ink-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="menu" @click.outside="menu = false" x-transition x-cloak
                                 class="absolute right-0 mt-2 w-56 card shadow-pop py-2 z-50">
                                <div class="px-4 py-2 border-b border-ink-100">
                                    <div class="font-semibold text-ink-900 text-sm truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-ink-400 truncate">{{ auth()->user()->email }}</div>
                                    <span class="mt-1.5 badge {{ auth()->user()->isOrganizer() ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">{{ auth()->user()->role->label() }}</span>
                                </div>
                                @if(auth()->user()->isOrganizer())
                                    <a href="{{ route('organizer.events.index') }}" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">Mes événements</a>
                                    <a href="{{ route('organizer.scanner') }}" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">Scanner des billets</a>
                                @else
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">Mes commandes</a>
                                @endif
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="w-full text-left px-4 py-2 text-sm text-brand-600 hover:bg-brand-50">Déconnexion</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-ink-700 hover:text-brand-600">Connexion</a>
                        <a href="{{ route('register') }}" class="btn-brand btn-sm">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if (session('success'))
        <div class="container-app mt-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center gap-2">
                <x-icon name="check-circle" class="w-5 h-5 shrink-0"/> {{ session('success') }}
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="container-app mt-4">
            <div class="rounded-xl bg-brand-50 border border-brand-200 text-brand-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ───────────────────────── CONTENU ───────────────────────── --}}
    <main class="flex-1 {{ $wide ? '' : 'container-app py-8 sm:py-10' }}">
        {{ $slot }}
    </main>

    {{-- ───────────────────────── FOOTER ───────────────────────── --}}
    <footer class="bg-ink-900 text-ink-200 mt-16">
        <div class="container-app py-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="grid place-items-center w-8 h-8 rounded-lg bg-brand-500 text-white"><x-icon name="ticket" class="w-5 h-5"/></span>
                    <span class="font-display font-extrabold text-lg text-white">EventPass</span>
                </div>
                <p class="text-sm text-ink-300 leading-relaxed">La billetterie événementielle simple : créez, vendez, scannez. Paiement Mobile Money &amp; carte bancaire.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Découvrir</h4>
                <ul class="space-y-2 text-sm text-ink-300">
                    <li><a href="{{ route('events.index') }}" class="hover:text-white">Tous les événements</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Devenir organisateur</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Paiement</h4>
                <ul class="space-y-2 text-sm text-ink-300">
                    <li class="flex items-center gap-2"><x-icon name="phone" class="w-4 h-4 text-ink-400"/> Wave</li>
                    <li class="flex items-center gap-2"><x-icon name="phone" class="w-4 h-4 text-ink-400"/> Orange Money</li>
                    <li class="flex items-center gap-2"><x-icon name="credit-card" class="w-4 h-4 text-ink-400"/> Carte bancaire</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Projet</h4>
                <p class="text-sm text-ink-300">ESTM — Master 1<br>Développement web 2.0</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="container-app py-4 text-xs text-ink-400">© {{ date('Y') }} EventPass — Plateforme SaaS de billetterie.</div>
        </div>
    </footer>

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
