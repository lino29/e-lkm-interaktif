<div class="space-y-4">
    <flux:heading size="xl">Remedial</flux:heading>
    @forelse ($attempts as $attempt)
        <flux:card wire:key="remedial-{{ $attempt->id }}"><div class="font-semibold">{{ $attempt->assessment->title }}</div><flux:text>{{ $attempt->assessment->module->title }} · Nilai {{ $attempt->total_score }}/{{ $attempt->max_score }}</flux:text><flux:button class="mt-3" size="sm" :href="route('murid.assessments.show', $attempt->assessment)" wire:navigate>Ulangi Asesmen</flux:button></flux:card>
    @empty
        <flux:text>Tidak ada remedial aktif.</flux:text>
    @endforelse
</div>
