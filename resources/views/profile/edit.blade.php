<x-site-layout title="Mon profil">
    <h1 class="font-display font-extrabold text-3xl text-ink-900 mb-6">Mon profil</h1>

    <div class="max-w-2xl space-y-6">
        <div class="card-pad">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card-pad">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card-pad">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-site-layout>
