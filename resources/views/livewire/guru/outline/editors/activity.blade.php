@php($activity = $activities->firstWhere('id', (int) ($form['linked_model_id'] ?? 0)))

<div class="space-y-4">
    <flux:field>
        <flux:label>Judul Aktivitas</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    @if ($form['section_type'] === 'activity_item')
        <flux:field>
            <flux:label>Jenis Aktivitas</flux:label>
            <flux:select wire:model="form.linked_model_id">
                <flux:select.option value="">Pilih aktivitas</flux:select.option>
                @foreach ($activities as $activityOption)
                    <flux:select.option value="{{ $activityOption->id }}">{{ \Illuminate\Support\Str::headline($activityOption->phase) }} - {{ $activityOption->title }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="form.linked_model_id" />
        </flux:field>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>Instruksi</flux:label>
                <flux:textarea rows="5" wire:model="form.content" placeholder="{{ $activity?->prompt }}" />
                <flux:error name="form.content" />
            </flux:field>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                <div class="text-sm font-medium">Ringkasan Aktivitas</div>
                @if ($activity)
                    <flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text>
                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Tipe jawaban: {{ \Illuminate\Support\Str::headline($activity->input_type) }}</div>
                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $activity->prompt }}</div>
                    <flux:button class="mt-3" size="sm" :href="route('guru.activities')" wire:navigate>Edit Detail Aktivitas</flux:button>
                @else
                    <flux:text>Pilih aktivitas untuk melihat instruksi dan tipe jawaban.</flux:text>
                @endif
            </div>
        </div>
    @else
        <flux:field>
            <flux:label>Deskripsi Kelompok Aktivitas</flux:label>
            <flux:textarea rows="3" wire:model="form.content" />
        </flux:field>
    @endif
</div>
