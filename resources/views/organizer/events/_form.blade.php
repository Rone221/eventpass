@php
    $isEdit = isset($event);
    $existingTypes = old('ticket_types', $isEdit
        ? $event->ticketTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'price' => $t->price, 'quantity' => $t->quantity, 'sold' => $t->sold])->toArray()
        : [['id' => '', 'name' => 'Standard', 'price' => 5000, 'quantity' => 100, 'sold' => 0]]);
@endphp

<form method="POST"
      action="{{ $isEdit ? route('organizer.events.update', $event) : route('organizer.events.store') }}"
      enctype="multipart/form-data"
      class="space-y-6 max-w-3xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    {{-- Image de couverture --}}
    <div class="card-pad" x-data="imageUpload(@js($isEdit && $event->image_path ? \App\Support\Media::url($event->image_path) : null))">
        <h2 class="font-display font-bold text-lg text-ink-900 mb-4">Image de couverture</h2>
        <div class="flex items-start gap-5">
            <div class="relative w-48 h-28 rounded-xl overflow-hidden bg-ink-100 shrink-0 border border-ink-100">
                <template x-if="preview">
                    <img :src="preview" class="absolute inset-0 w-full h-full object-cover" alt="Aperçu">
                </template>
                <template x-if="!preview">
                    <div class="absolute inset-0 grid place-items-center text-ink-300">
                        <x-icon name="camera" class="w-8 h-8"/>
                    </div>
                </template>
            </div>
            <div class="flex-1">
                <input type="file" name="image" accept="image/*" @change="pick($event)"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:text-brand-700 file:px-4 file:py-2 file:font-semibold hover:file:bg-brand-100 file:cursor-pointer">
                <p class="text-xs text-ink-400 mt-2">JPG, PNG ou WebP — 4 Mo max. Recommandé : 1200×600px.</p>
                <template x-if="preview">
                    <button type="button" @click="clearImg()" class="mt-2 text-xs text-brand-600 hover:underline">Retirer l'image</button>
                </template>
                @if ($isEdit && $event->image_path)
                    <label class="mt-2 flex items-center gap-2 text-xs text-ink-500">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-ink-300 text-brand-500 focus:ring-brand-400" @change="if($event.target.checked) clearImg()">
                        Supprimer l'image actuelle
                    </label>
                @endif
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="card-pad space-y-5">
        <h2 class="font-display font-bold text-lg text-ink-900">Informations</h2>

        <div>
            <label class="label">Titre de l'événement *</label>
            <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
                   placeholder="Ex : DevFest Dakar 2026" class="w-full">
        </div>

        <div>
            <label class="label">Description</label>
            <textarea name="description" rows="4" placeholder="Décrivez votre événement…" class="w-full">{{ old('description', $event->description ?? '') }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label">Lieu *</label>
                <input type="text" name="venue" value="{{ old('venue', $event->venue ?? '') }}" required placeholder="Ex : Grand Théâtre" class="w-full">
            </div>
            <div>
                <label class="label">Ville *</label>
                <input type="text" name="city" value="{{ old('city', $event->city ?? '') }}" required placeholder="Ex : Dakar" class="w-full">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label">Début *</label>
                <input type="datetime-local" name="starts_at" required
                       value="{{ old('starts_at', isset($event) ? $event->starts_at->format('Y-m-d\TH:i') : '') }}" class="w-full">
            </div>
            <div>
                <label class="label">Fin</label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', isset($event) && $event->ends_at ? $event->ends_at->format('Y-m-d\TH:i') : '') }}" class="w-full">
            </div>
        </div>

        <div>
            <label class="label">Statut *</label>
            <select name="status" class="w-full">
                @foreach (\App\Enums\EventStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $event->status->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <p class="text-xs text-ink-400 mt-1.5">Seuls les événements « Publié » apparaissent dans le catalogue public.</p>
        </div>
    </div>

    {{-- Catégories de billets --}}
    <div class="card-pad" x-data="{ types: @js(array_values($existingTypes)) }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-lg text-ink-900">Catégories de billets</h2>
            <button type="button" @click="types.push({ id: '', name: '', price: 0, quantity: 50, sold: 0 })" class="btn-ghost btn-sm">+ Ajouter</button>
        </div>

        <div class="space-y-3">
            <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-ink-400 px-1">
                <div class="col-span-5">Nom</div><div class="col-span-3">Prix (F)</div><div class="col-span-3">Quantité</div><div class="col-span-1"></div>
            </div>
            <template x-for="(type, index) in types" :key="index">
                <div class="grid grid-cols-12 gap-2 items-center">
                    <input type="hidden" :name="`ticket_types[${index}][id]`" :value="type.id">
                    <input type="text" :name="`ticket_types[${index}][name]`" x-model="type.name" placeholder="VIP" required class="col-span-5">
                    <input type="number" :name="`ticket_types[${index}][price]`" x-model="type.price" min="0" required class="col-span-3">
                    <input type="number" :name="`ticket_types[${index}][quantity]`" x-model="type.quantity" min="1" required class="col-span-3">
                    <button type="button" @click="types.splice(index, 1)" x-show="types.length > 1" class="col-span-1 text-brand-500 hover:text-brand-700 text-xl">×</button>
                </div>
            </template>
        </div>
        <p class="text-xs text-ink-400 mt-3">Une catégorie déjà vendue ne peut être supprimée ni descendre sous le nombre vendu.</p>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn-brand">{{ $isEdit ? 'Enregistrer' : 'Créer l\'événement' }}</button>
        <a href="{{ $isEdit ? route('organizer.events.show', $event) : route('organizer.events.index') }}" class="btn-ghost">Annuler</a>
    </div>
</form>

<script>
    function imageUpload(initial) {
        return {
            preview: initial,
            pick(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.preview = URL.createObjectURL(file);
            },
            clearImg() {
                this.preview = null;
                const input = document.querySelector('input[name=image]');
                if (input) input.value = '';
            },
        };
    }
</script>
