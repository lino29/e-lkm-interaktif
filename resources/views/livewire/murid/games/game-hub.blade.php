<div class="space-y-6">
    <x-elkm.page-header
        title="Games Edukatif"
        subtitle="Pilih tantangan energi terbarukan, bermain secara interaktif, dan tingkatkan skor terbaikmu."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Games</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-elkm-primary to-emerald-700 p-6 text-white shadow-lg md:p-8">
        <div class="absolute -right-16 -top-16 size-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-20 right-28 size-44 rounded-full bg-amber-300/15"></div>
        <div class="relative max-w-2xl">
            <div class="text-xs font-black uppercase tracking-[0.24em] text-emerald-100">Zona Tantangan</div>
            <h2 class="mt-3 text-2xl font-black md:text-3xl">Belajar energi sambil bermain</h2>
            <p class="mt-2 text-sm leading-6 text-emerald-50">Setiap game memiliki cara bermain yang berbeda. Selesaikan pertanyaan, dapatkan umpan balik langsung, lalu coba lagi untuk memperbaiki skor.</p>
        </div>
    </section>

    <div class="grid gap-4 rounded-3xl border border-elkm-line bg-white p-4 shadow-sm md:grid-cols-[1fr_16rem]">
        <flux:field>
            <flux:label>Cari game</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari judul atau topik game..." />
        </flux:field>
        <flux:field>
            <flux:label>Jenis permainan</flux:label>
            <flux:select wire:model.live="gameType">
                <flux:select.option value="">Semua jenis</flux:select.option>
                @foreach ($gameTypeLabels as $type => $label)
                    <flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @forelse ($games as $game)
            @php
                $latestAttempt = $latestAttempts->get($game->id);
                $activeAttempt = $activeAttempts->get($game->id);
                $scorePercent = $latestAttempt && (float) $latestAttempt->max_score > 0
                    ? min(100, (int) round(((float) $latestAttempt->score / (float) $latestAttempt->max_score) * 100))
                    : 0;
            @endphp

            <article wire:key="game-card-{{ $game->id }}" class="group flex h-full flex-col overflow-hidden rounded-3xl border border-elkm-line bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-elkm-primary/50 hover:shadow-xl">
                <div class="relative bg-gradient-to-br from-elkm-surface to-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-elkm-primary text-sm font-black text-white shadow-md transition group-hover:rotate-3 group-hover:scale-105">
                            {{ $game->icon ?? 'GM' }}
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-elkm-primary shadow-sm">{{ $gameTypeLabels[$game->type] ?? \Illuminate\Support\Str::headline($game->type) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-black leading-tight text-elkm-text">{{ $game->title }}</h3>
                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-elkm-muted">{{ $game->description }}</p>
                </div>

                <div class="flex flex-1 flex-col gap-4 p-5 pt-0">
                    <div class="grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="rounded-xl bg-elkm-surface/70 px-2 py-3">
                            <div class="font-black text-elkm-text">{{ $game->active_items_count }}</div>
                            <div class="mt-0.5 text-elkm-muted">Tantangan</div>
                        </div>
                        <div class="rounded-xl bg-elkm-surface/70 px-2 py-3">
                            <div class="font-black text-elkm-text">{{ $game->finished_attempts_count }}</div>
                            <div class="mt-0.5 text-elkm-muted">Selesai dimainkan</div>
                        </div>
                    </div>

                    @if ($latestAttempt)
                        <div class="rounded-2xl border border-elkm-line bg-white p-3">
                            <div class="flex items-center justify-between gap-3 text-xs font-bold">
                                <span class="text-elkm-muted">Skor terakhir</span>
                                <span class="text-elkm-primary">{{ (float) $latestAttempt->score }}/{{ (float) $latestAttempt->max_score }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-elkm-surface">
                                <div class="h-full rounded-full bg-elkm-primary transition-all duration-700" style="width: {{ $scorePercent }}%"></div>
                            </div>
                        </div>
                    @elseif ($activeAttempt)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold text-amber-700">Permainan sedang berlangsung · Attempt {{ $activeAttempt->attempt_number }}</div>
                    @else
                        <div class="rounded-2xl border border-dashed border-elkm-line p-3 text-xs font-semibold text-elkm-muted">Belum dimainkan. Jadilah penantang berikutnya!</div>
                    @endif

                    <flux:button wire:click="startGame({{ $game->id }})" wire:loading.attr="disabled" variant="primary" class="mt-auto w-full">
                        <span wire:loading.remove wire:target="startGame({{ $game->id }})">{{ $activeAttempt ? 'Lanjutkan Game' : ($latestAttempt ? 'Main Lagi' : 'Mainkan Sekarang') }}</span>
                        <span wire:loading wire:target="startGame({{ $game->id }})">Menyiapkan game...</span>
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-4">
                <x-elkm.empty-state title="Game belum tersedia" description="Game aktif dengan tantangan yang sudah disiapkan guru akan muncul di sini." />
            </div>
        @endforelse
    </div>
</div>
