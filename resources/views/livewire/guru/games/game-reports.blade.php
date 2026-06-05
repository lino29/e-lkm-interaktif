@php
    $selectedType = $selectedGame?->type ?? $gameType;
    $selectedTypeLabel = $gameTypeLabels[$selectedType] ?? $selectedType;
@endphp

<div class="space-y-5">
    <x-elkm.page-header
        title="Kelola Games"
        subtitle="Atur game dan soal untuk murid."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Games</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="inline-flex rounded-xl border border-elkm-line bg-white p-1 shadow-sm">
        <button
            type="button"
            wire:click="$set('activeTab', 'editor')"
            @class([
                'rounded-lg px-4 py-2 text-sm font-semibold transition',
                'bg-elkm-primary text-white' => $activeTab === 'editor',
                'text-elkm-muted hover:bg-elkm-surface' => $activeTab !== 'editor',
            ])
        >
            Kelola Soal
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'report')"
            @class([
                'rounded-lg px-4 py-2 text-sm font-semibold transition',
                'bg-elkm-primary text-white' => $activeTab === 'report',
                'text-elkm-muted hover:bg-elkm-surface' => $activeTab !== 'report',
            ])
        >
            Laporan
        </button>
    </div>

    @if ($activeTab === 'editor')
        <div class="grid gap-5 xl:grid-cols-[22rem_1fr]">
            <aside class="space-y-5">
                <section class="rounded-xl border border-elkm-line bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-elkm-text">Game</h3>
                            <p class="mt-1 text-xs text-elkm-muted">{{ $games->count() }} game tersedia</p>
                        </div>
                        <button type="button" wire:click="createGame" class="rounded-lg bg-elkm-primary px-3 py-2 text-xs font-bold text-white">
                            Game Baru
                        </button>
                    </div>

                    <label class="mt-4 grid gap-1 text-sm font-semibold text-elkm-text">
                        Pilih Game
                        <select wire:change="selectGame($event.target.value)" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                            @if (! $selectedGameId)
                                <option value="">Game baru</option>
                            @endif
                            @foreach ($games as $game)
                                <option value="{{ $game->id }}" @selected($selectedGameId === $game->id)>
                                    {{ $game->title }} ({{ $game->items_count ?? 0 }} soal)
                                </option>
                            @endforeach
                        </select>
                    </label>

                    @if ($selectedGame)
                        <div class="mt-4 rounded-lg border border-elkm-line bg-elkm-surface/60 p-3">
                            <div class="font-bold text-elkm-text">{{ $selectedGame->title }}</div>
                            <div class="mt-1 text-xs text-elkm-muted">{{ $selectedTypeLabel }} · {{ $selectedGame->items->count() }} soal</div>
                            <div class="mt-3 flex gap-2">
                                <button type="button" wire:click="editGame({{ $selectedGame->id }})" class="rounded-lg border border-elkm-line px-3 py-1.5 text-xs font-semibold text-elkm-text">Edit</button>
                                <button type="button" wire:click="deleteGame({{ $selectedGame->id }})" wire:confirm="Hapus atau nonaktifkan game ini?" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                            </div>
                        </div>
                    @endif
                </section>

                <form wire:submit="saveGame" class="space-y-4 rounded-xl border border-elkm-line bg-white p-4 shadow-sm">
                    <div>
                        <h3 class="text-base font-bold text-elkm-text">{{ $editingGameId ? 'Edit Game' : 'Game Baru' }}</h3>
                        <p class="mt-1 text-xs text-elkm-muted">Isi judul, tipe, dan status game.</p>
                    </div>

                    <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                        Judul Game
                        <input type="text" wire:model="gameTitle" class="rounded-lg border border-elkm-line px-3 py-2 text-sm" placeholder="Contoh: Kuis Energi Surya">
                        @error('gameTitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                        Jenis Game
                        <select wire:model="gameType" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                            @foreach ($gameTypeChoices as $type)
                                <option value="{{ $type }}">{{ $gameTypeLabels[$type] ?? $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                        Deskripsi Singkat
                        <textarea wire:model="gameDescription" rows="3" class="rounded-lg border border-elkm-line px-3 py-2 text-sm"></textarea>
                    </label>

                    <label class="flex items-center gap-2 text-sm font-semibold text-elkm-text">
                        <input type="checkbox" wire:model="gameIsActive" class="rounded border-elkm-line">
                        Tampilkan ke murid
                    </label>

                    <details class="rounded-lg border border-elkm-line bg-elkm-surface/40 px-3 py-2">
                        <summary class="cursor-pointer text-sm font-semibold text-elkm-muted">Pengaturan lanjutan</summary>
                        <div class="mt-3 grid gap-3">
                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                Kode
                                <input type="text" wire:model="gameCode" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                @error('gameCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                Slug
                                <input type="text" wire:model="gameSlug" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                @error('gameSlug') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Ikon
                                    <input type="text" wire:model="gameIcon" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Urutan
                                    <input type="number" min="0" wire:model="gameSortOrder" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                            </div>
                        </div>
                    </details>

                    <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-elkm-primary px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                        Simpan Game
                    </button>
                </form>
            </aside>

            <main class="space-y-5">
                <section class="rounded-xl border border-elkm-line bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-elkm-text">Daftar Soal</h3>
                            <p class="mt-1 text-xs text-elkm-muted">{{ $selectedGame ? $selectedGame->title : 'Simpan game terlebih dahulu' }}</p>
                        </div>
                        <button type="button" wire:click="createItem" @disabled(! $selectedGameId) class="rounded-lg bg-elkm-primary px-3 py-2 text-xs font-bold text-white disabled:opacity-40">
                            Soal Baru
                        </button>
                    </div>

                    <div class="mt-4 divide-y divide-elkm-line overflow-hidden rounded-lg border border-elkm-line">
                        @forelse ($selectedGame?->items ?? [] as $item)
                            <div wire:key="teacher-game-item-{{ $item->id }}" class="grid gap-3 bg-white p-3 md:grid-cols-[4.75rem_1fr_auto] md:items-center">
                                <div class="overflow-hidden rounded-lg border border-elkm-line bg-elkm-surface">
                                    @if ($item->media_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->media_path) }}" alt="{{ $item->question_text ?? $item->prompt }}" class="aspect-video w-full object-cover">
                                    @elseif ($item->media_url)
                                        <div class="grid aspect-video place-items-center px-2 text-center text-xs font-bold text-elkm-primary">{{ $item->media_url }}</div>
                                    @else
                                        <div class="grid aspect-video place-items-center text-[11px] text-elkm-muted">Tanpa gambar</div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-bold text-elkm-text">{{ $item->question_text ?? $item->prompt ?? 'Soal tanpa judul' }}</div>
                                    <div class="mt-1 text-xs text-elkm-muted">Soal {{ $item->sort_order }} · {{ (float) $item->score }} poin · {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="editItem({{ $item->id }})" class="rounded-lg border border-elkm-line px-3 py-1.5 text-xs font-semibold text-elkm-text">Edit</button>
                                    <button type="button" wire:click="deleteItem({{ $item->id }})" wire:confirm="Hapus atau nonaktifkan soal ini?" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white px-4 py-8 text-center text-sm text-elkm-muted">Belum ada soal.</div>
                        @endforelse
                    </div>
                </section>

                @if ($selectedGame)
                    <form wire:submit="saveItem" class="space-y-5 rounded-xl border border-elkm-line bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-bold text-elkm-text">{{ $editingItemId ? 'Edit Soal' : 'Soal Baru' }}</h3>
                                <p class="mt-1 text-xs text-elkm-muted">{{ $selectedTypeLabel }}</p>
                            </div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-elkm-text">
                                <input type="checkbox" wire:model="itemIsActive" class="rounded border-elkm-line">
                                Aktif
                            </label>
                        </div>

                        @error('selectedGameId') <div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div> @enderror

                        <div class="grid gap-4 lg:grid-cols-[1fr_14rem]">
                            <div class="space-y-4">
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Pertanyaan / Skenario
                                    <textarea wire:model="itemQuestionText" rows="3" class="rounded-lg border border-elkm-line px-3 py-2 text-sm" placeholder="Tulis pertanyaan yang akan dilihat murid"></textarea>
                                </label>

                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Instruksi Singkat
                                    <textarea wire:model="itemPrompt" rows="2" class="rounded-lg border border-elkm-line px-3 py-2 text-sm" placeholder="Contoh: Jawab sebelum waktu habis"></textarea>
                                </label>
                            </div>

                            <div class="space-y-2">
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Ilustrasi
                                    <input type="file" wire:model="itemImage" accept="image/jpeg,image/png,image/webp" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                    @error('itemImage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </label>
                                <div class="overflow-hidden rounded-lg border border-elkm-line bg-elkm-surface">
                                    @if ($itemImage)
                                        <img src="{{ $itemImage->temporaryUrl() }}" alt="Ilustrasi soal" class="aspect-video w-full object-cover">
                                    @elseif ($itemMediaPath)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($itemMediaPath) }}" alt="Ilustrasi soal" class="aspect-video w-full object-cover">
                                    @elseif ($itemMediaUrl)
                                        <div class="grid aspect-video place-items-center px-3 text-center text-sm font-bold text-elkm-primary">{{ $itemMediaUrl }}</div>
                                    @else
                                        <div class="grid aspect-video place-items-center text-xs text-elkm-muted">Belum ada gambar</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($selectedType === 'puzzle_order')
                            <section class="space-y-3 rounded-lg border border-elkm-line bg-elkm-surface/40 p-3">
                                <h4 class="text-sm font-bold text-elkm-text">Urutan Komponen</h4>
                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($puzzleComponents as $index => $component)
                                        <label wire:key="puzzle-component-field-{{ $index }}" class="grid gap-1 text-sm font-semibold text-elkm-text">
                                            Langkah {{ $index + 1 }}
                                            <input type="text" wire:model="puzzleComponents.{{ $index }}.text" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @elseif ($selectedType === 'decision_mission')
                            <section class="space-y-3 rounded-lg border border-elkm-line bg-elkm-surface/40 p-3">
                                <h4 class="text-sm font-bold text-elkm-text">Pilihan Keputusan</h4>
                                <div class="grid gap-3">
                                    @foreach ($decisionChoices as $index => $choice)
                                        <div wire:key="decision-choice-field-{{ $index }}" class="grid gap-3 rounded-lg bg-white p-3 lg:grid-cols-[1fr_7rem_1fr]">
                                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                                Pilihan {{ $choice['key'] ?: chr(65 + $index) }}
                                                <input type="text" wire:model="decisionChoices.{{ $index }}.text" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                            </label>
                                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                                Skor
                                                <input type="number" min="0" wire:model="decisionChoices.{{ $index }}.score_delta" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                            </label>
                                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                                Umpan Balik
                                                <input type="text" wire:model="decisionChoices.{{ $index }}.feedback" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @else
                            <section class="space-y-3 rounded-lg border border-elkm-line bg-elkm-surface/40 p-3">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <h4 class="text-sm font-bold text-elkm-text">Pilihan Jawaban</h4>
                                    <label class="grid gap-1 text-sm font-semibold text-elkm-text sm:w-64">
                                        Jawaban Benar
                                        <select wire:model="quizCorrectKey" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                            @foreach ($quizOptions as $optionKey => $optionText)
                                                <option value="{{ $optionKey }}">{{ $optionText !== '' ? $optionText : 'Pilihan '.$loop->iteration }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($quizOptions as $optionKey => $optionText)
                                        <label wire:key="quiz-option-field-{{ $optionKey }}" class="grid gap-1 text-sm font-semibold text-elkm-text">
                                            Pilihan {{ $loop->iteration }}
                                            <input type="text" wire:model="quizOptions.{{ $optionKey }}" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                        </label>
                                    @endforeach
                                </div>
                            </section>

                            @if ($selectedType === 'image_guess')
                                <div class="grid gap-3 md:grid-cols-[1fr_9rem]">
                                    <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                        Petunjuk
                                        <input type="text" wire:model="itemHint" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                    </label>
                                    <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                        Penalti
                                        <input type="number" min="0" wire:model="itemHintPenalty" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                    </label>
                                </div>
                            @endif
                        @endif

                        <div class="grid gap-3 md:grid-cols-[1fr_8rem_8rem]">
                            <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                Penjelasan Setelah Menjawab
                                <textarea wire:model="itemExplanation" rows="2" class="rounded-lg border border-elkm-line px-3 py-2 text-sm"></textarea>
                            </label>
                            @if ($selectedType !== 'decision_mission')
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Skor
                                    <input type="number" min="0" step="0.01" wire:model="itemScore" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                            @endif
                            @if ($selectedType === 'timed_quiz')
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Detik
                                    <input type="number" min="1" wire:model="itemTimeLimitSeconds" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                            @endif
                        </div>

                        <details class="rounded-lg border border-elkm-line bg-elkm-surface/40 px-3 py-2">
                            <summary class="cursor-pointer text-sm font-semibold text-elkm-muted">Lainnya</summary>
                            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_8rem]">
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Label Gambar / URL
                                    <input type="text" wire:model="itemMediaUrl" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                                <label class="grid gap-1 text-sm font-semibold text-elkm-text">
                                    Urutan
                                    <input type="number" min="0" wire:model="itemSortOrder" class="rounded-lg border border-elkm-line px-3 py-2 text-sm">
                                </label>
                            </div>
                        </details>

                        <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-elkm-primary px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                            Simpan Soal
                        </button>
                    </form>
                @else
                    <div class="rounded-xl border border-dashed border-elkm-line bg-white px-4 py-8 text-center text-sm text-elkm-muted">
                        Simpan game baru sebelum menambahkan soal.
                    </div>
                @endif
            </main>
        </div>
    @endif

    @if ($activeTab === 'report')
        <div class="space-y-5">
            <flux:card>
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:field>
                        <flux:label>Game</flux:label>
                        <flux:select wire:model.live="game_id">
                            <flux:select.option value="">Semua Game</flux:select.option>
                            @foreach ($games as $game)
                                <flux:select.option value="{{ $game->id }}">{{ $game->title }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model.live="attempt_status">
                            <flux:select.option value="">Semua Status</flux:select.option>
                            <flux:select.option value="in_progress">Sedang dimainkan</flux:select.option>
                            <flux:select.option value="finished">Selesai</flux:select.option>
                            <flux:select.option value="abandoned">Ditinggalkan</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Cari Murid</flux:label>
                        <flux:input wire:model.live.debounce.400ms="search" placeholder="Nama murid" />
                    </flux:field>
                </div>
            </flux:card>

            <div class="grid gap-4 md:grid-cols-5">
                <flux:card><div class="text-sm text-elkm-muted">Total Attempt</div><div class="mt-1 text-2xl font-semibold">{{ $summary['total_attempts'] }}</div></flux:card>
                <flux:card><div class="text-sm text-elkm-muted">Selesai</div><div class="mt-1 text-2xl font-semibold">{{ $summary['finished_attempts'] }}</div></flux:card>
                <flux:card><div class="text-sm text-elkm-muted">Pemain</div><div class="mt-1 text-2xl font-semibold">{{ $summary['player_count'] }}</div></flux:card>
                <flux:card><div class="text-sm text-elkm-muted">Rata-rata</div><div class="mt-1 text-2xl font-semibold">{{ $summary['average_score'] ?? '-' }}</div></flux:card>
                <flux:card><div class="text-sm text-elkm-muted">Tertinggi</div><div class="mt-1 text-2xl font-semibold">{{ $summary['highest_score'] ?? '-' }}</div></flux:card>
            </div>

            <flux:card class="space-y-4">
                <flux:heading>Attempt Terbaru</flux:heading>
                <div class="overflow-x-auto rounded-lg border border-elkm-line">
                    <table class="min-w-full divide-y divide-elkm-line text-sm">
                        <thead class="bg-elkm-surface text-left text-elkm-muted">
                            <tr>
                                <th class="px-4 py-3">Murid</th>
                                <th class="px-4 py-3">Game</th>
                                <th class="px-4 py-3">Skor</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Selesai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-elkm-line">
                            @forelse ($attempts as $attempt)
                                <tr wire:key="game-report-attempt-{{ $attempt->id }}">
                                    <td class="px-4 py-3 font-medium">{{ $attempt->user->name }}</td>
                                    <td class="px-4 py-3">{{ $attempt->game->title }}</td>
                                    <td class="px-4 py-3">{{ $attempt->score }}/{{ $attempt->max_score }}</td>
                                    <td class="px-4 py-3"><flux:badge size="sm">{{ $attempt->status }}</flux:badge></td>
                                    <td class="px-4 py-3">{{ $attempt->finished_at?->diffForHumans() ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-elkm-muted">Belum ada attempt game sesuai filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </flux:card>
        </div>
    @endif
</div>
