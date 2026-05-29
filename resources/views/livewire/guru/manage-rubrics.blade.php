<div class="space-y-6">
    <flux:heading size="xl">Kelola Rubrik</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Soal</flux:label><flux:select wire:model="question_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($questions as $question)<flux:select.option value="{{ $question->id }}">{{ \Illuminate\Support\Str::limit($question->question_text, 70) }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Kriteria</flux:label><flux:input wire:model="criterion" /></flux:field>
        <flux:field><flux:label>Level</flux:label><flux:input wire:model="level" /></flux:field>
        <flux:field><flux:label>Skor</flux:label><flux:input type="number" step="0.01" wire:model="score" /></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Deskripsi</flux:label><flux:textarea wire:model="description" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Rubrik</flux:button>
    </form>
    @foreach ($rubrics as $rubric)
        <flux:card wire:key="rubric-{{ $rubric->id }}"><div class="font-semibold">{{ $rubric->criterion }} · {{ $rubric->score }}</div><flux:text>{{ \Illuminate\Support\Str::limit($rubric->question->question_text, 90) }}</flux:text></flux:card>
    @endforeach
</div>
