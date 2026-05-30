<div class="space-y-4">
    <flux:heading size="xl">Remedial</flux:heading>
    @forelse ($remedials as $remedial)
        <flux:card wire:key="remedial-{{ $remedial['attempt']->id }}">
            <div class="font-semibold">{{ $remedial['assessment']->title }}</div>
            <flux:text>{{ $remedial['assessment']->module->title }} - Nilai terakhir {{ $remedial['attempt']->total_score }}/{{ $remedial['attempt']->max_score }} - KKTP {{ $remedial['assessment']->kktp }}</flux:text>
            <div class="mt-3 grid gap-2 text-sm md:grid-cols-2">
                <div>Percobaan dipakai: {{ $remedial['attemptsUsed'] }}</div>
                <div>Sisa percobaan: {{ $remedial['remainingAttempts'] }}</div>
            </div>
            <flux:callout class="mt-3">{{ $remedial['recommendation'] }}</flux:callout>
            @if ($remedial['remainingAttempts'] > 0)
                <flux:button class="mt-3" size="sm" :href="route('murid.assessments.show', $remedial['assessment'])" wire:navigate>Ulangi Asesmen</flux:button>
            @else
                <flux:callout class="mt-3">Batas percobaan sudah habis. Hubungi guru untuk tindak lanjut.</flux:callout>
            @endif
        </flux:card>
    @empty
        <flux:text>Tidak ada remedial aktif.</flux:text>
    @endforelse
</div>
