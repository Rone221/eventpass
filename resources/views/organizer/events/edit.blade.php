<x-site-layout title="Modifier l'événement">
    <a href="{{ route('organizer.events.show', $event) }}" class="text-sm text-ink-400 hover:text-brand-600">← Retour</a>
    <h1 class="font-display font-extrabold text-3xl text-ink-900 mt-2 mb-6">Modifier « {{ $event->title }} »</h1>
    @include('organizer.events._form')
</x-site-layout>
