<div class="space-y-6">
    <flux:heading size="xl">Kelola Soal</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Asesmen</flux:label><flux:select wire:model="assessment_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($assessments as $assessment)<flux:select.option value="{{ $assessment->id }}">{{ $assessment->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Tipe soal</flux:label><flux:select wire:model="question_type">@foreach (['multiple_choice','complex_multiple_choice','true_false','matching','short_answer','essay'] as $type)<flux:select.option value="{{ $type }}">{{ \Illuminate\Support\Str::headline($type) }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Pertanyaan</flux:label><flux:textarea wire:model="question_text" /></flux:field>
        <flux:field><flux:label>Opsi JSON</flux:label><flux:textarea wire:model="options_json" /></flux:field>
        <flux:field><flux:label>Kunci JSON</flux:label><flux:textarea wire:model="correct_answer_json" /></flux:field>
        <flux:field><flux:label>Bobot</flux:label><flux:input type="number" step="0.01" wire:model="weight" /></flux:field>
        <flux:field><flux:label>Keyword uraian/isian</flux:label><flux:input wire:model="keywords" placeholder="matahari, energi, terbarukan" /></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Jawaban acuan uraian</flux:label><flux:textarea wire:model="reference_answer" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Soal</flux:button>
    </form>
    @foreach ($questions as $question)
        <flux:card wire:key="question-{{ $question->id }}"><div class="font-semibold">{{ $question->question_text }}</div><flux:text>{{ \Illuminate\Support\Str::headline($question->question_type) }} · {{ $question->assessment->title }}</flux:text></flux:card>
    @endforeach
</div>
