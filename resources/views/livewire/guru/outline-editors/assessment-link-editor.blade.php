<div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
    <div class="text-sm font-medium">Preview Asesmen</div>
    @php($assessment = $assessments->firstWhere('id', (int) ($form['linked_model_id'] ?? 0)))
    @if ($assessment)
        <flux:text>KKTP {{ $assessment->kktp }}. Maks {{ $assessment->max_attempts }} percobaan. {{ $assessment->questions->count() }} soal.</flux:text>
    @else
        <flux:text>Pilih linked model Asesmen untuk menampilkan preview.</flux:text>
    @endif
</div>
