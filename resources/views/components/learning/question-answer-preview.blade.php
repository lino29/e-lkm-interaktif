@props(['question'])

@php
    $type = $question->question_type;
    $options = is_array($question->options ?? null) ? $question->options : [];
    $optionLabel = fn ($key, $index) => is_string($key) && ! is_numeric($key)
        ? $key
        : chr(65 + $index);
    $optionText = fn ($option) => is_scalar($option) ? (string) $option : '';
@endphp

<div class="mt-4 space-y-3">
    @if ($type === 'multiple_choice')
        <div class="grid gap-2">
            @forelse ($options as $key => $option)
                <div class="flex items-start gap-3 rounded-xl border border-elkm-line bg-elkm-surface/70 px-3 py-2.5">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full border border-elkm-line bg-white text-xs font-bold text-elkm-text">
                        {{ $optionLabel($key, $loop->index) }}
                    </span>
                    <span class="pt-0.5 text-sm font-medium leading-relaxed text-elkm-text">{{ $optionText($option) }}</span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-elkm-line px-3 py-2 text-sm text-elkm-muted">
                    Belum ada pilihan jawaban.
                </div>
            @endforelse
        </div>
    @elseif ($type === 'complex_multiple_choice')
        <div class="rounded-xl border border-elkm-line bg-elkm-surface/60 px-3 py-2 text-xs font-medium text-elkm-muted">
            Murid dapat memilih lebih dari satu jawaban.
        </div>

        <div class="grid gap-2">
            @forelse ($options as $key => $option)
                <div class="flex items-start gap-3 rounded-xl border border-elkm-line bg-elkm-surface/70 px-3 py-2.5">
                    <span class="mt-1 size-4 shrink-0 rounded border border-elkm-line bg-white"></span>
                    <span class="text-sm font-medium leading-relaxed text-elkm-text">{{ $optionText($option) }}</span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-elkm-line px-3 py-2 text-sm text-elkm-muted">
                    Belum ada pilihan jawaban.
                </div>
            @endforelse
        </div>
    @elseif ($type === 'true_false')
        @php
            $trueFalseOptions = $options ?: ['True' => 'Benar', 'False' => 'Salah'];
        @endphp

        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($trueFalseOptions as $key => $option)
                <div class="flex items-center gap-3 rounded-xl border border-elkm-line bg-elkm-surface/70 px-3 py-2.5">
                    <span class="size-4 shrink-0 rounded-full border border-elkm-line bg-white"></span>
                    <span class="text-sm font-semibold text-elkm-text">{{ $optionText($option) ?: \Illuminate\Support\Str::headline($key) }}</span>
                </div>
            @endforeach
        </div>
    @elseif ($type === 'matching')
        @php
            $leftOptions = is_array($options['left'] ?? null) ? $options['left'] : [];
            $rightOptions = is_array($options['right'] ?? null) ? $options['right'] : [];
        @endphp

        <div class="grid gap-3 lg:grid-cols-[1fr_16rem]">
            <div class="space-y-2">
                @forelse ($leftOptions as $leftOption)
                    <div class="grid gap-2 rounded-xl border border-elkm-line bg-elkm-surface/70 px-3 py-2.5 sm:grid-cols-[1fr_12rem] sm:items-center">
                        <span class="text-sm font-medium text-elkm-text">{{ $optionText($leftOption) }}</span>
                        <span class="rounded-lg border border-elkm-line bg-white px-3 py-2 text-xs font-medium text-elkm-muted">
                            Pilih pasangan
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-elkm-line px-3 py-2 text-sm text-elkm-muted">
                        Belum ada pasangan soal.
                    </div>
                @endforelse
            </div>

            <div class="rounded-xl border border-elkm-line bg-elkm-surface/70 p-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-elkm-muted">Pilihan</div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($rightOptions as $rightOption)
                        <span class="rounded-lg border border-elkm-line bg-white px-2.5 py-1.5 text-xs font-medium text-elkm-text">
                            {{ $optionText($rightOption) }}
                        </span>
                    @empty
                        <span class="text-xs text-elkm-muted">Belum ada pilihan pasangan.</span>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif ($type === 'short_answer')
        <div class="rounded-xl border border-elkm-line bg-white px-3 py-3 text-sm text-elkm-muted">
            Jawaban singkat
        </div>
    @elseif ($type === 'essay')
        <div class="min-h-28 rounded-xl border border-elkm-line bg-white px-3 py-3 text-sm text-elkm-muted">
            Jawaban uraian
        </div>
    @else
        <div class="min-h-24 rounded-xl border border-elkm-line bg-white px-3 py-3 text-sm text-elkm-muted">
            Tulis jawaban
        </div>
    @endif
</div>
