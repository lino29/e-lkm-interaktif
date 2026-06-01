@php($assessment = $assessments->firstWhere('id', (int) ($form['linked_model_id'] ?? 0)))

<div class="space-y-4">
    <flux:field>
        <flux:label>Judul Asesmen</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    <flux:field>
        <flux:label>Hubungkan ke Asesmen</flux:label>
        <flux:select wire:model="form.linked_model_id">
            <flux:select.option value="">Pilih asesmen</flux:select.option>
            @foreach ($assessments as $assessmentOption)
                <flux:select.option value="{{ $assessmentOption->id }}">{{ $assessmentOption->title }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="form.linked_model_id" />
    </flux:field>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">KKTP</div>
            <div class="mt-1 text-xl font-semibold">{{ $assessment?->kktp ?? '-' }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Maksimal Percobaan</div>
            <div class="mt-1 text-xl font-semibold">{{ $assessment?->max_attempts ?? '-' }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Jumlah Soal</div>
            <div class="mt-1 text-xl font-semibold">{{ $assessment?->questions->count() ?? 0 }}</div>
        </div>
    </div>

    @if ($assessment)
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
            <div class="font-medium">Ringkasan jumlah soal per group</div>
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                @foreach ($assessment->questions->groupBy('question_group') as $group => $questions)
                    <div class="rounded-md bg-zinc-50 p-3 text-sm dark:bg-zinc-800">
                        {{ \Illuminate\Support\Str::headline($group ?: 'Tanpa Group') }}: {{ $questions->count() }} soal
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex flex-wrap gap-2">
        <flux:button type="button" :href="route('guru.questions')" wire:navigate>Kelola Soal</flux:button>
        <flux:button type="button" variant="ghost" :href="route('guru.assessments')" wire:navigate>Preview Asesmen</flux:button>
    </div>
</div>
