<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">{{ $assessment->title }}</flux:heading>
            <flux:text>KKTP {{ $assessment->kktp }} - Maks {{ $assessment->max_attempts }} percobaan</flux:text>
        </div>

        @if ($currentGroup)
            <div class="rounded-full border border-elkm-line bg-white px-4 py-2 text-sm font-semibold text-elkm-muted shadow-sm">
                Bagian {{ $currentGroupIndex + 1 }} dari {{ $questionGroups->count() }}
            </div>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger">{{ session('error') }}</flux:callout>
    @endif

    @if ($latestAttempt)
        <flux:card>
            <div class="font-semibold">Attempt terakhir: {{ $latestAttempt->total_score }}/{{ $latestAttempt->max_score }}</div>
            <flux:text>Status {{ $latestAttempt->status }} - {{ $latestAttempt->feedback }}</flux:text>
        </flux:card>
    @endif

    @if ($currentAttempt === null)
        <flux:callout>Batas percobaan asesmen sudah tercapai atau asesmen sudah tuntas.</flux:callout>
    @endif

    @if ($currentGroup)
        <div class="rounded-3xl border border-elkm-line bg-white/85 p-4 shadow-sm md:p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                @foreach ($questionGroups as $index => $group)
                    @php
                        $isActiveGroup = $index === $currentGroupIndex;
                        $isSavedGroup = $savedQuestionGroups[$group['key']] ?? false;
                    @endphp

                    <button
                        type="button"
                        wire:click="$set('currentGroupIndex', {{ $index }})"
                        @class([
                            'rounded-2xl border px-4 py-3 text-left text-sm transition',
                            'border-elkm-primary bg-elkm-primary text-white shadow-sm' => $isActiveGroup,
                            'border-elkm-line bg-elkm-surface text-elkm-text hover:border-elkm-primary/60' => ! $isActiveGroup,
                        ])
                    >
                        <span class="block font-semibold">{{ $group['label'] }}</span>
                        <span @class([
                            'mt-1 block text-xs',
                            'text-white/80' => $isActiveGroup,
                            'text-elkm-muted' => ! $isActiveGroup,
                        ])>
                            {{ $group['questions']->count() }} soal
                            @if ($isSavedGroup)
                                - tersimpan
                            @endif
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <form wire:submit="submit" class="space-y-6">
            <section class="space-y-4" wire:key="assessment-question-group-{{ $currentGroup['key'] }}">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-elkm-muted">Jenis soal</div>
                        <h2 class="text-xl font-bold text-elkm-text">{{ $currentGroup['label'] }}</h2>
                    </div>
                    <div class="text-sm text-elkm-muted">
                        {{ $currentGroup['questions']->count() }} soal pada bagian ini
                    </div>
                </div>

                @foreach ($currentGroup['questions'] as $question)
                    @php
                        $options = is_array($question->options ?? null) ? $question->options : [];
                        $optionText = fn ($option) => is_scalar($option) ? (string) $option : '';
                    @endphp

                    <div class="rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6" wire:key="answer-question-{{ $question->id }}">
                        <div class="text-base font-bold leading-relaxed text-elkm-text">
                            {{ $question->order }}. {{ $question->question_text }}
                        </div>

                        <div class="mt-5">
                            @if ($question->question_type === 'multiple_choice')
                                <div class="grid gap-3">
                                    @foreach ($options as $key => $option)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 text-sm font-medium text-elkm-text transition hover:border-elkm-primary/60">
                                            <input
                                                type="radio"
                                                class="mt-1 size-4 shrink-0"
                                                wire:model="answers.{{ $question->id }}"
                                                value="{{ $key }}"
                                                @disabled(! $isAttemptOpen)
                                            >
                                            <span>{{ $key }}. {{ $optionText($option) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->question_type === 'complex_multiple_choice')
                                <div class="mb-3 rounded-2xl border border-elkm-line bg-elkm-surface/70 px-4 py-3 text-sm text-elkm-muted">
                                    Pilih semua jawaban yang benar.
                                </div>

                                <div class="grid gap-3">
                                    @foreach ($options as $key => $option)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 text-sm font-medium text-elkm-text transition hover:border-elkm-primary/60">
                                            <input
                                                type="checkbox"
                                                class="mt-1 size-4 shrink-0 rounded"
                                                wire:model="answers.{{ $question->id }}"
                                                value="{{ $key }}"
                                                @disabled(! $isAttemptOpen)
                                            >
                                            <span>{{ $key }}. {{ $optionText($option) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->question_type === 'true_false')
                                @php
                                    $trueFalseOptions = $options ?: ['True' => 'Benar', 'False' => 'Salah'];
                                @endphp

                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($trueFalseOptions as $key => $option)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 text-sm font-semibold text-elkm-text transition hover:border-elkm-primary/60">
                                            <input
                                                type="radio"
                                                class="size-4 shrink-0"
                                                wire:model="answers.{{ $question->id }}"
                                                value="{{ $key }}"
                                                @disabled(! $isAttemptOpen)
                                            >
                                            <span>{{ $optionText($option) ?: \Illuminate\Support\Str::headline($key) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($question->question_type === 'matching')
                                @php
                                    $hasStructuredMatchingOptions = isset($options['left'], $options['right']) && is_array($options['left']) && is_array($options['right']);
                                    $leftOptions = $hasStructuredMatchingOptions ? $options['left'] : array_keys($options);
                                    $rightOptions = $hasStructuredMatchingOptions ? $options['right'] : array_values($options);
                                @endphp

                                <div class="space-y-4">
                                    <div class="rounded-2xl border border-elkm-line bg-elkm-surface/70 px-4 py-3 text-sm text-elkm-muted">
                                        Pilih pasangan yang tepat untuk setiap baris.
                                    </div>

                                    <div class="grid gap-3">
                                        @foreach ($leftOptions as $leftKey => $leftValue)
                                            @php
                                                $answerKey = $hasStructuredMatchingOptions ? $leftKey : $leftValue;
                                            @endphp

                                            <div class="grid gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/60 p-4 md:grid-cols-[1fr_16rem] md:items-center">
                                                <div class="text-sm font-semibold text-elkm-text">
                                                    {{ $hasStructuredMatchingOptions ? "{$leftKey}. {$leftValue}" : $leftValue }}
                                                </div>
                                                <select
                                                    class="w-full rounded-xl border border-elkm-line bg-white px-3 py-2 text-sm text-elkm-text"
                                                    wire:model="answers.{{ $question->id }}.{{ $answerKey }}"
                                                    @disabled(! $isAttemptOpen)
                                                >
                                                    <option value="">Pilih pasangan...</option>
                                                    @foreach ($rightOptions as $rightKey => $rightValue)
                                                        <option value="{{ $hasStructuredMatchingOptions ? $rightKey : $rightValue }}">
                                                            {{ $hasStructuredMatchingOptions ? "{$rightKey}. {$rightValue}" : $rightValue }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <textarea
                                    class="min-h-32 w-full rounded-2xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 text-sm text-elkm-text outline-none transition focus:border-elkm-primary focus:bg-white"
                                    wire:model="answers.{{ $question->id }}"
                                    placeholder="Ketik jawaban Anda secara lengkap di sini..."
                                    @disabled(! $isAttemptOpen)
                                ></textarea>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="sticky bottom-4 z-10 rounded-3xl border border-elkm-line bg-white/95 p-4 shadow-lg backdrop-blur">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        wire:click="previousGroup"
                        class="rounded-xl border border-elkm-line px-4 py-2.5 text-sm font-semibold text-elkm-text transition hover:border-elkm-primary disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($currentGroupIndex === 0)
                    >
                        Sebelumnya
                    </button>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button
                            type="button"
                            wire:click="saveCurrentGroup"
                            wire:loading.attr="disabled"
                            class="rounded-xl border border-elkm-primary px-4 py-2.5 text-sm font-semibold text-elkm-primary transition hover:bg-elkm-primary/10 disabled:cursor-not-allowed disabled:opacity-50"
                            @disabled(! $isAttemptOpen)
                        >
                            Simpan
                        </button>

                        @if ($currentGroupIndex < $lastGroupIndex)
                            <button
                                type="button"
                                wire:click="nextGroup"
                                wire:loading.attr="disabled"
                                class="rounded-xl bg-elkm-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-elkm-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                                @disabled(! $isAttemptOpen)
                            >
                                Selanjutnya
                            </button>
                        @else
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="rounded-xl bg-elkm-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-elkm-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                                @disabled(! $isAttemptOpen)
                            >
                                Kirim dan Nilai Otomatis
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    @else
        <flux:callout>Belum ada soal pada asesmen ini.</flux:callout>
    @endif
</div>
