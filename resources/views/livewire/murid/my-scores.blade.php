<div class="space-y-4">
    <flux:heading size="xl">Nilai Saya</flux:heading>
    @foreach ($attempts as $attempt)
        <flux:card wire:key="score-{{ $attempt->id }}"><div class="font-semibold">{{ $attempt->assessment->title }}</div><flux:text>{{ $attempt->assessment->module->title }} · Attempt {{ $attempt->attempt_number }} · {{ $attempt->total_score }}/{{ $attempt->max_score }} · {{ $attempt->status }}</flux:text></flux:card>
    @endforeach
</div>
