<div class="space-y-6">
    <x-elkm.page-header
        title="Kelola Games"
        subtitle="Aktifkan atau nonaktifkan game edukatif dan pantau statistik sistem."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Games</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card><div class="text-sm text-elkm-muted">Murid</div><div class="mt-1 text-2xl font-semibold">{{ $studentCount }}</div></flux:card>
        <flux:card><div class="text-sm text-elkm-muted">Total Attempt</div><div class="mt-1 text-2xl font-semibold">{{ $summary['total_attempts'] }}</div></flux:card>
        <flux:card><div class="text-sm text-elkm-muted">Rata-rata Skor</div><div class="mt-1 text-2xl font-semibold">{{ $summary['average_score'] ?? '-' }}</div></flux:card>
        <flux:card><div class="text-sm text-elkm-muted">Skor Tertinggi</div><div class="mt-1 text-2xl font-semibold">{{ $summary['highest_score'] ?? '-' }}</div></flux:card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($games as $game)
            <flux:card wire:key="admin-game-{{ $game->id }}" class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="grid size-12 place-items-center rounded-2xl bg-elkm-primary/10 text-sm font-black text-elkm-primary">
                        {{ $game->icon ?? 'GM' }}
                    </div>
                    <flux:badge size="sm" color="{{ $game->is_active ? 'green' : 'zinc' }}">{{ $game->is_active ? 'Aktif' : 'Nonaktif' }}</flux:badge>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-elkm-text">{{ $game->title }}</h3>
                    <p class="mt-1 text-sm leading-6 text-elkm-muted">{{ $game->description }}</p>
                </div>

                <div class="rounded-2xl border border-elkm-line bg-elkm-surface/70 p-3 text-sm text-elkm-muted">
                    <div>Attempt: <span class="font-semibold text-elkm-text">{{ $game->attempts_count }}</span></div>
                    <div>Rata-rata: <span class="font-semibold text-elkm-text">{{ $game->finished_average_score ? round((float) $game->finished_average_score, 2) : '-' }}</span></div>
                    <div>Tertinggi: <span class="font-semibold text-elkm-text">{{ $game->finished_highest_score ? round((float) $game->finished_highest_score, 2) : '-' }}</span></div>
                </div>

                <flux:button wire:click="toggleGame({{ $game->id }})" wire:loading.attr="disabled" class="w-full">
                    {{ $game->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </flux:button>
            </flux:card>
        @endforeach
    </div>
</div>
