<div class="space-y-6">
    <div>
        <flux:heading size="xl">Dashboard Admin</flux:heading>
        <flux:text>Ringkasan fondasi sistem E-LKM Interaktif.</flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $label => $value)
            <flux:card wire:key="admin-stat-{{ $label }}">
                <flux:text>{{ $label }}</flux:text>
                <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $value }}</div>
            </flux:card>
        @endforeach
    </div>
</div>
