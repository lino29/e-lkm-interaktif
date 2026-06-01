<div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
    <div class="text-sm font-medium">Preview Aktivitas</div>
    @php($activity = $activities->firstWhere('id', (int) ($form['linked_model_id'] ?? 0)))
    @if ($activity)
        <flux:text>{{ $activity->phase }} - {{ $activity->input_type }}</flux:text>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $activity->prompt }}</p>
    @else
        <flux:text>Pilih linked model Aktivitas untuk menampilkan preview.</flux:text>
    @endif
</div>
