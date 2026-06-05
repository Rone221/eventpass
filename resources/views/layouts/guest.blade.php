<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EventPass') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-ink-700 antialiased">
    <div class="min-h-screen grid lg:grid-cols-2">
        {{-- Volet branding (aubergine) --}}
        <div class="hidden lg:flex relative bg-ink-900 text-white p-12 flex-col justify-between overflow-hidden">
            <div class="absolute inset-0 opacity-40"
                 style="background-image: radial-gradient(circle at 20% 20%, #F05537 0, transparent 40%), radial-gradient(circle at 80% 80%, #7C3AED 0, transparent 45%);"></div>
            <a href="{{ route('events.index') }}" class="relative flex items-center gap-2">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-brand-500 text-white"><x-icon name="ticket" class="w-6 h-6"/></span>
                <span class="font-display font-extrabold text-2xl">EventPass</span>
            </a>
            <div class="relative">
                <h2 class="font-display font-extrabold text-4xl leading-tight">Créez, vendez,<br>scannez vos billets.</h2>
                <p class="mt-4 text-ink-200 max-w-sm">La billetterie événementielle qui accepte Wave, Orange Money, Free Money et la carte bancaire.</p>
                <div class="mt-8 flex gap-3">
                    <span class="chip bg-white/10 text-white"><x-icon name="phone" class="w-4 h-4"/> Wave</span>
                    <span class="chip bg-white/10 text-white"><x-icon name="phone" class="w-4 h-4"/> Orange Money</span>
                    <span class="chip bg-white/10 text-white"><x-icon name="credit-card" class="w-4 h-4"/> CB</span>
                </div>
            </div>
            <div class="relative text-xs text-ink-300">ESTM — Master 1 · Développement web 2.0</div>
        </div>

        {{-- Volet formulaire --}}
        <div class="flex items-center justify-center p-6 sm:p-12 bg-ink-50/40">
            <div class="w-full max-w-md">
                <a href="{{ route('events.index') }}" class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                    <span class="grid place-items-center w-10 h-10 rounded-xl bg-brand-500 text-white"><x-icon name="ticket" class="w-6 h-6"/></span>
                    <span class="font-display font-extrabold text-2xl text-ink-900">EventPass</span>
                </a>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
