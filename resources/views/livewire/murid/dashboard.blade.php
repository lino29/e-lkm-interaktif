<div class="space-y-6">
    <flux:heading size="xl">Dashboard Murid</flux:heading>
    <div class="grid gap-4 md:grid-cols-4">
        @foreach ($stats as $label => $value)
            <flux:card wire:key="murid-stat-{{ $label }}"><flux:text>{{ $label }}</flux:text><div class="mt-2 text-3xl font-semibold">{{ $value }}</div></flux:card>
        @endforeach
    </div>

    <section class="space-y-3">
        <flux:heading>Progress Modul</flux:heading>
        @forelse ($moduleProgress as $item)
            <flux:card wire:key="dashboard-module-progress-{{ $item['module']->id }}">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="font-semibold">{{ $item['module']->title }}</div>
                        <flux:text>{{ $item['module']->learningUnits->count() }} kegiatan belajar</flux:text>
                    </div>
                    <div class="min-w-48">
                        <div class="mb-1 flex justify-between text-sm text-zinc-600 dark:text-zinc-300">
                            <span>Progress</span>
                            <span>{{ $item['percentage'] }}%</span>
                        </div>
                        <flux:progress :value="$item['percentage']" />
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:text>Belum ada modul aktif.</flux:text>
        @endforelse
    </section>
</div>
