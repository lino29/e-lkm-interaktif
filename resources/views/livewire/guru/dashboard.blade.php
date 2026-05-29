<div class="space-y-6">
    <flux:heading size="xl">Dashboard Guru</flux:heading>
    <div class="grid gap-4 md:grid-cols-4">
        @foreach ($stats as $label => $value)
            <flux:card wire:key="guru-stat-{{ $label }}">
                <flux:text>{{ $label }}</flux:text>
                <div class="mt-2 text-3xl font-semibold">{{ $value }}</div>
            </flux:card>
        @endforeach
    </div>
</div>
