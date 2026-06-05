<x-guest-layout>
    <h1 class="font-display font-extrabold text-3xl text-ink-900">Bon retour</h1>
    <p class="text-ink-400 mt-1 mb-8">Connectez-vous pour accéder à vos billets et événements.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="label">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-ink-300 text-brand-500 focus:ring-brand-400" name="remember">
                <span class="ms-2 text-sm text-ink-600">Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm link" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-brand w-full">Se connecter</button>

        <p class="text-center text-sm text-ink-500">
            Pas encore de compte ? <a href="{{ route('register') }}" class="link">Créer un compte</a>
        </p>
    </form>
</x-guest-layout>
