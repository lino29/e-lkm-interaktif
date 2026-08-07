<div class="grid items-start gap-5 lg:grid-cols-[230px_minmax(0,1fr)] lg:gap-7">
    <nav aria-label="Navigasi pengaturan akun" class="card-elkm grid gap-2 p-3 lg:sticky lg:top-7">
        <x-elkm.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" wire:navigate icon="PR">Profil</x-elkm.nav-link>
        <x-elkm.nav-link :href="route('security.edit')" :active="request()->routeIs('security.edit')" wire:navigate icon="SC">Keamanan</x-elkm.nav-link>
        <x-elkm.nav-link :href="route('appearance.edit')" :active="request()->routeIs('appearance.edit')" wire:navigate icon="UI">Tampilan</x-elkm.nav-link>
    </nav>

    <section aria-labelledby="settings-section-heading" class="card-elkm min-w-0 p-5 md:p-8">
        <h2 id="settings-section-heading" class="text-xl font-bold text-elkm-text">{{ $heading ?? '' }}</h2>
        <p class="mt-1 text-sm leading-6 text-elkm-muted">{{ $subheading ?? '' }}</p>

        <div class="mt-6 w-full max-w-2xl [--color-accent:var(--color-elkm-primary)] [--color-accent-content:var(--color-elkm-primary)] [--color-accent-foreground:white]">
            {{ $slot }}
        </div>
    </section>
</div>
