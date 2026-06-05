<x-site-layout title="Nouvel événement">
    <a href="{{ route('organizer.events.index') }}" class="text-sm text-ink-400 hover:text-brand-600">← Mes événements</a>
    <h1 class="font-display font-extrabold text-3xl text-ink-900 mt-2 mb-6">Créer un événement</h1>
    @include('organizer.events._form')
</x-site-layout>
