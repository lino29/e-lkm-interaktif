@php($assessment = $assessments->firstWhere('id', (int) ($form['linked_model_id'] ?? 0)))
@php($questionGroup = $form['slug'] ?: ($form['content_json']['question_group'] ?? null))

<div class="space-y-4">
    <flux:field>
        <flux:label>Nama Kelompok Soal</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    <flux:field>
        <flux:label>Tipe Kelompok</flux:label>
        <flux:select wire:model="form.slug">
            <flux:select.option value="pilihan_ganda_biasa">Pilihan Ganda Biasa</flux:select.option>
            <flux:select.option value="pilihan_ganda_kompleks">Pilihan Ganda Kompleks</flux:select.option>
            <flux:select.option value="benar_salah">Benar Salah</flux:select.option>
            <flux:select.option value="isian_uraian_singkat">Isian/Uraian Singkat</flux:select.option>
            <flux:select.option value="menjodohkan">Menjodohkan</flux:select.option>
        </flux:select>
    </flux:field>

    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
        <div class="text-sm text-zinc-500 dark:text-zinc-400">Jumlah soal</div>
        <div class="mt-1 text-xl font-semibold">
            {{ $assessment && $questionGroup ? $assessment->questions->where('question_group', $questionGroup)->count() : 0 }}
        </div>
    </div>

    <flux:button type="button" :href="route('guru.questions')" wire:navigate>Kelola Soal</flux:button>
</div>
