@php
    $itemsCount = $items->count();
    $answeredProgress = min($itemsCount, $currentItemIndex + ($awaitingContinue ? 1 : 0));
    $progressPercent = $itemsCount > 0 ? (int) round(($answeredProgress / $itemsCount) * 100) : 0;
    $isFinalItem = $itemsCount > 0 && $currentItemIndex >= $itemsCount - 1;
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <div class="mb-3 flex flex-wrap items-center gap-2 text-sm font-medium text-elkm-muted">
                <a href="{{ route('murid.dashboard') }}" wire:navigate class="hover:text-elkm-primary">Dashboard</a>
                <span>/</span>
                <a href="{{ route('murid.games.index') }}" wire:navigate class="hover:text-elkm-primary">Games</a>
                <span>/</span>
                <span>{{ $game->title }}</span>
            </div>
            <h2 class="m-0 text-2xl font-bold text-elkm-text md:text-3xl">{{ $game->title }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-elkm-muted">{{ $game->description }}</p>
        </div>

        <div class="w-full rounded-2xl border border-elkm-line bg-white p-4 shadow-sm md:w-72">
            <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                <span class="text-elkm-muted">Attempt {{ $attempt->attempt_number }}</span>
                <span class="text-elkm-primary">{{ $progressLabel }}</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-elkm-surface">
                <div class="h-full rounded-full bg-elkm-primary transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="mt-2 text-xs font-semibold text-elkm-muted">{{ $progressPercent }}% selesai</div>
        </div>
    </div>

    @if ($awaitingContinue && $lastFeedback)
        <div @class([
            'rounded-3xl border px-5 py-4 shadow-sm',
            'border-emerald-200 bg-emerald-50' => $lastAnswerCorrect,
            'border-amber-200 bg-amber-50' => ! $lastAnswerCorrect,
        ])>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div @class([
                        'text-xs font-black uppercase',
                        'text-emerald-700' => $lastAnswerCorrect,
                        'text-amber-700' => ! $lastAnswerCorrect,
                    ])>{{ $lastAnswerCorrect ? 'Jawaban Tepat' : 'Coba Analisis Lagi' }}</div>
                    <div class="mt-1 text-sm font-semibold leading-6 text-elkm-text">{{ $lastFeedback }}</div>
                    @if ($lastScore !== null)
                        <div class="mt-1 text-sm font-bold text-elkm-muted">Skor jawaban: {{ (float) $lastScore }}</div>
                    @endif
                </div>
                <button
                    type="button"
                    wire:click="continueAfterFeedback"
                    wire:loading.attr="disabled"
                    class="rounded-xl bg-elkm-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-elkm-primary/90 disabled:opacity-50"
                >
                    {{ $isFinalItem ? 'Lihat Hasil' : 'Lanjut' }}
                </button>
            </div>
        </div>
    @endif

    @if ($game->type === 'puzzle_order' && $currentItem)
        @php($options = (array) $currentItem->options)

        <section class="space-y-5 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
            <div>
                <h3 class="text-xl font-bold text-elkm-text">{{ $currentItem->question_text }}</h3>
                @if ($currentItem->prompt)
                    <p class="mt-1 text-sm leading-6 text-elkm-muted">{{ $currentItem->prompt }}</p>
                @endif
            </div>

            <div class="grid gap-3">
                @foreach ($puzzleOrder as $index => $componentKey)
                    <div wire:key="puzzle-component-{{ $componentKey }}" class="grid gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/70 p-4 md:grid-cols-[3rem_1fr_auto] md:items-center">
                        <div class="grid size-10 place-items-center rounded-xl bg-elkm-primary text-sm font-black text-white">{{ $index + 1 }}</div>
                        <div>
                            <div class="font-semibold text-elkm-text">{{ $options[$componentKey] ?? $componentKey }}</div>
                            <div class="text-xs text-elkm-muted">Komponen: {{ $componentKey }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="movePuzzleItem({{ $index }}, 'up')" class="rounded-xl border border-elkm-line px-3 py-2 text-sm font-semibold text-elkm-text disabled:opacity-40" @disabled($index === 0)>Naik</button>
                            <button type="button" wire:click="movePuzzleItem({{ $index }}, 'down')" class="rounded-xl border border-elkm-line px-3 py-2 text-sm font-semibold text-elkm-text disabled:opacity-40" @disabled($index === count($puzzleOrder) - 1)>Turun</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t border-elkm-line pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-elkm-muted">Baterai bersifat opsional, tetapi urutan aliran energi tetap harus benar.</p>
                <button type="button" wire:click="submitPuzzle" wire:loading.attr="disabled" class="rounded-xl bg-elkm-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-elkm-primary/90 disabled:opacity-50">
                    Periksa Urutan
                </button>
            </div>
        </section>
    @endif

    @if ($game->type === 'timed_quiz' && $currentItem)
        @php($options = (array) $currentItem->options)
        @php($correctKey = (string) data_get($currentItem->correct_answer, 'key'))
        @php($mediaSource = $currentItem->media_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($currentItem->media_path) : $currentItem->media_url)
        @php($hasImageMedia = $currentItem->media_path || \Illuminate\Support\Str::startsWith((string) $mediaSource, ['http://', 'https://', '/storage/', '/uploads/']))

        <section
            class="space-y-5 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6"
            wire:key="quick-quiz-item-{{ $currentItem->id }}"
            x-data="{ seconds: {{ (int) ($currentItem->time_limit_seconds ?? 10) }}, done: @js($awaitingContinue) }"
            x-init="if (! done) { const timer = setInterval(() => { if (done) { clearInterval(timer); return; } if (seconds > 0) { seconds--; return; } done = true; clearInterval(timer); $wire.timeoutQuizAnswer(); }, 1000) }"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-elkm-text">{{ $currentItem->question_text }}</h3>
                    @if ($currentItem->prompt)
                        <p class="mt-1 text-sm leading-6 text-elkm-muted">{{ $currentItem->prompt }}</p>
                    @endif
                </div>
                <div class="rounded-2xl bg-elkm-primary px-4 py-2 text-center text-white">
                    <div class="text-xs font-semibold uppercase">Timer</div>
                    <div class="text-2xl font-black" x-text="seconds"></div>
                </div>
            </div>

            @if ($mediaSource)
                <div class="overflow-hidden rounded-3xl border border-elkm-line bg-elkm-surface">
                    @if ($hasImageMedia)
                        <img src="{{ $mediaSource }}" alt="{{ $currentItem->question_text }}" class="max-h-64 w-full object-contain p-2">
                    @else
                        <div class="grid min-h-32 place-items-center px-4 py-8 text-center text-4xl font-black text-elkm-primary">{{ $mediaSource }}</div>
                    @endif
                </div>
            @endif

            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($options as $key => $option)
                    @php($optionKey = (string) $key)
                    <button
                        type="button"
                        wire:key="quick-option-{{ $currentItem->id }}-{{ $key }}"
                        wire:click="submitQuizAnswer('{{ $key }}')"
                        wire:loading.attr="disabled"
                        @click="done = true"
                        @disabled($awaitingContinue)
                        @class([
                            'rounded-2xl border px-4 py-3 text-left text-sm font-semibold transition disabled:cursor-not-allowed',
                            'border-elkm-line bg-elkm-surface/70 text-elkm-text hover:border-elkm-primary hover:bg-white' => ! $awaitingContinue,
                            'border-emerald-300 bg-emerald-50 text-emerald-800' => $awaitingContinue && $optionKey === $correctKey,
                            'border-red-300 bg-red-50 text-red-800' => $awaitingContinue && $selectedAnswerKey === $optionKey && $lastAnswerCorrect === false,
                            'border-elkm-line bg-white text-elkm-muted opacity-65' => $awaitingContinue && $selectedAnswerKey !== $optionKey && $optionKey !== $correctKey,
                        ])
                    >
                        {{ $key }}. {{ $option }}
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    @if ($game->type === 'decision_mission' && $currentItem)
        @php($mediaSource = $currentItem->media_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($currentItem->media_path) : $currentItem->media_url)
        @php($hasImageMedia = $currentItem->media_path || \Illuminate\Support\Str::startsWith((string) $mediaSource, ['http://', 'https://', '/storage/', '/uploads/']))

        <section class="space-y-5 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
            <div>
                <h3 class="text-xl font-bold text-elkm-text">{{ $currentItem->question_text }}</h3>
                @if ($currentItem->prompt)
                    <p class="mt-1 text-sm leading-6 text-elkm-muted">{{ $currentItem->prompt }}</p>
                @endif
            </div>

            @if ($mediaSource)
                <div class="overflow-hidden rounded-3xl border border-elkm-line bg-elkm-surface">
                    @if ($hasImageMedia)
                        <img src="{{ $mediaSource }}" alt="{{ $currentItem->question_text }}" class="max-h-64 w-full object-contain p-2">
                    @else
                        <div class="grid min-h-32 place-items-center px-4 py-8 text-center text-4xl font-black text-elkm-primary">{{ $mediaSource }}</div>
                    @endif
                </div>
            @endif

            <div class="grid gap-3">
                @foreach ((array) $currentItem->options as $choice)
                    @php($choiceKey = (string) data_get($choice, 'key'))
                    <button
                        type="button"
                        wire:key="mission-choice-{{ $currentItem->id }}-{{ $choiceKey }}"
                        wire:click="chooseDecision('{{ $choiceKey }}')"
                        wire:loading.attr="disabled"
                        @disabled($awaitingContinue)
                        @class([
                            'rounded-2xl border px-4 py-4 text-left transition disabled:cursor-not-allowed',
                            'border-elkm-line bg-elkm-surface/70 hover:border-elkm-primary hover:bg-white' => ! $awaitingContinue,
                            'border-elkm-primary bg-elkm-primary/10' => $awaitingContinue && $selectedAnswerKey === $choiceKey,
                            'border-elkm-line bg-white opacity-65' => $awaitingContinue && $selectedAnswerKey !== $choiceKey,
                        ])
                    >
                        <span class="block text-sm font-bold text-elkm-primary">{{ $choiceKey }}</span>
                        <span class="mt-1 block font-semibold text-elkm-text">{{ data_get($choice, 'text') }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    @if ($game->type === 'image_guess' && $currentItem)
        @php($options = (array) $currentItem->options)
        @php($hintIsUsed = (bool) ($hintUsed[$currentItem->id] ?? false))
        @php($correctKey = (string) data_get($currentItem->correct_answer, 'key'))
        @php($mediaSource = $currentItem->media_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($currentItem->media_path) : $currentItem->media_url)
        @php($hasImageMedia = $currentItem->media_path || \Illuminate\Support\Str::startsWith((string) $mediaSource, ['http://', 'https://', '/storage/', '/uploads/']))

        <section class="space-y-5 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
            <div class="grid gap-5 md:grid-cols-[18rem_1fr] md:items-center">
                <div class="overflow-hidden rounded-3xl border border-elkm-line bg-elkm-surface">
                    @if ($mediaSource && $hasImageMedia)
                        <img src="{{ $mediaSource }}" alt="{{ $currentItem->question_text }}" class="aspect-square w-full object-contain p-4">
                    @else
                        <div class="grid aspect-square place-items-center px-4 text-center text-5xl font-black text-elkm-primary">
                            {{ $mediaSource ?: '?' }}
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-bold text-elkm-text">{{ $currentItem->question_text }}</h3>
                    <p class="mt-1 text-sm leading-6 text-elkm-muted">Jawaban benar bernilai {{ $currentItem->score }} poin. Petunjuk mengurangi 5 poin.</p>

                    <button type="button" wire:click="revealHint({{ $currentItem->id }})" class="mt-4 rounded-xl border border-elkm-primary px-4 py-2.5 text-sm font-semibold text-elkm-primary disabled:opacity-50" @disabled($hintIsUsed || $awaitingContinue)>
                        {{ $hintIsUsed ? 'Petunjuk Dibuka' : 'Buka Petunjuk' }}
                    </button>

                    @if ($hintIsUsed)
                        <div class="mt-3 rounded-2xl border border-elkm-line bg-elkm-surface/70 px-4 py-3 text-sm text-elkm-muted">
                            {{ data_get($currentItem->config, 'hint') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach ($options as $key => $option)
                    @php($optionKey = (string) $key)
                    <button
                        type="button"
                        wire:key="image-option-{{ $currentItem->id }}-{{ $key }}"
                        wire:click="chooseImageAnswer('{{ $key }}')"
                        wire:loading.attr="disabled"
                        @disabled($awaitingContinue)
                        @class([
                            'rounded-2xl border px-4 py-3 text-left text-sm font-semibold transition disabled:cursor-not-allowed',
                            'border-elkm-line bg-elkm-surface/70 text-elkm-text hover:border-elkm-primary hover:bg-white' => ! $awaitingContinue,
                            'border-emerald-300 bg-emerald-50 text-emerald-800' => $awaitingContinue && $optionKey === $correctKey,
                            'border-red-300 bg-red-50 text-red-800' => $awaitingContinue && $selectedAnswerKey === $optionKey && $lastAnswerCorrect === false,
                            'border-elkm-line bg-white text-elkm-muted opacity-65' => $awaitingContinue && $selectedAnswerKey !== $optionKey && $optionKey !== $correctKey,
                        ])
                    >
                        {{ $option }}
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    @if (! $currentItem || ! in_array($game->type, ['puzzle_order', 'timed_quiz', 'decision_mission', 'image_guess'], true))
        <x-elkm.empty-state title="Game belum siap" description="Item game belum tersedia atau tipe game belum didukung." />
    @endif
</div>
