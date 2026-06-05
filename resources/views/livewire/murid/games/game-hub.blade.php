<div class="space-y-6">
    <x-elkm.page-header
        title="Games Edukatif"
        subtitle="Pilih game penguatan materi energi terbarukan, mainkan, lalu lihat skor terbaikmu."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Games</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @forelse ($games as $game)
            @php($latestAttempt = $latestAttempts->get($game->id))

            <flux:card wire:key="game-card-{{ $game->id }}" class="flex h-full flex-col gap-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-elkm-primary/10 text-sm font-black text-elkm-primary">
                        {{ $game->icon ?? 'GM' }}
                    </div>
                    <flux:badge size="sm" color="green">Aktif</flux:badge>
                </div>

                <div class="space-y-2">
                    <h3 class="text-lg font-bold leading-tight text-elkm-text">{{ $game->title }}</h3>
                    <p class="text-sm leading-6 text-elkm-muted">{{ $game->description }}</p>
                </div>

                <div class="mt-auto rounded-2xl border border-elkm-line bg-elkm-surface/70 p-3 text-sm">
                    @if ($latestAttempt)
                        <div class="font-semibold text-elkm-text">Skor terakhir {{ $latestAttempt->score }}/{{ $latestAttempt->max_score }}</div>
                        <div class="mt-1 text-xs text-elkm-muted">Selesai {{ $latestAttempt->finished_at?->diffForHumans() }}</div>
                    @else
                        <div class="font-semibold text-elkm-text">Belum dimainkan</div>
                        <div class="mt-1 text-xs text-elkm-muted">{{ $game->finished_attempts_count }} attempt selesai</div>
                    @endif
                </div>

                <flux:button wire:click="startGame({{ $game->id }})" wire:loading.attr="disabled" variant="primary" class="w-full">
                    Mainkan Sekarang
                </flux:button>
            </flux:card>
        @empty
            <div class="md:col-span-2 xl:col-span-4">
                <x-elkm.empty-state title="Belum ada game aktif" description="Game akan muncul setelah admin mengaktifkannya." />
            </div>
        @endforelse
    </div>
</div>
