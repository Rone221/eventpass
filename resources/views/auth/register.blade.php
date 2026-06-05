<x-guest-layout>
    <h1 class="font-display font-extrabold text-3xl text-ink-900">Créer un compte</h1>
    <p class="text-ink-400 mt-1 mb-8">Rejoignez EventPass en quelques secondes.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Rôle --}}
        <div>
            <label class="label">Je m'inscris en tant que</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="participant" class="peer sr-only" {{ old('role', 'participant') === 'participant' ? 'checked' : '' }}>
                    <div class="rounded-xl border border-ink-200 p-4 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-500 transition">
                        <x-icon name="ticket" class="w-7 h-7 mx-auto text-ink-500"/>
                        <div class="font-semibold text-ink-900 mt-2">Participant</div>
                        <div class="text-xs text-ink-400">J'achète des billets</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="organizer" class="peer sr-only" {{ old('role') === 'organizer' ? 'checked' : '' }}>
                    <div class="rounded-xl border border-ink-200 p-4 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-500 transition">
                        <x-icon name="megaphone" class="w-7 h-7 mx-auto text-ink-500"/>
                        <div class="font-semibold text-ink-900 mt-2">Organisateur</div>
                        <div class="text-xs text-ink-400">Je crée des événements</div>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div>
            <label for="name" class="label">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="phone" class="label">Téléphone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="77 000 00 00" class="w-full">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="label">Mot de passe</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirmation</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <button type="submit" class="btn-brand w-full">Créer mon compte</button>

        <p class="text-center text-sm text-ink-500">
            Déjà inscrit ? <a href="{{ route('login') }}" class="link">Se connecter</a>
        </p>
    </form>
</x-guest-layout>
