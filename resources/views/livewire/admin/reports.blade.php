<div class="space-y-6">
    <flux:heading size="xl">Laporan Sistem</flux:heading>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $label => $value)
            <flux:card wire:key="report-{{ $label }}">
                <flux:text>{{ \Illuminate\Support\Str::headline($label) }}</flux:text>
                <div class="mt-2 text-3xl font-semibold">{{ $value }}</div>
            </flux:card>
        @endforeach
    </div>
</div>
