<div class="space-y-6">
    <flux:heading size="xl">Laporan Sistem</flux:heading>
    
    <div class="flex items-center space-x-4">
        <flux:select wire:model.live="module_id" placeholder="Pilih Modul untuk Export" class="max-w-xs">
            <flux:select.option value="">Pilih Modul</flux:select.option>
            @foreach($modules as $module)
                <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
            @endforeach
        </flux:select>
        
        <flux:button wire:click="exportExcel" icon="document-text" variant="primary">Export Excel</flux:button>
        <flux:button wire:click="exportPdf" icon="document-arrow-down">Export PDF</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $label => $value)
            @if(is_array($value))
                <flux:card wire:key="report-{{ $label }}">
                    <flux:text>{{ \Illuminate\Support\Str::headline($label) }}</flux:text>
                    <div class="mt-2 space-y-1 text-sm">
                        @foreach($value as $subLabel => $subValue)
                            <div class="flex justify-between">
                                <span class="text-zinc-500 capitalize">{{ $subLabel }}</span>
                                <span class="font-semibold">{{ $subValue }}</span>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @else
                <flux:card wire:key="report-{{ $label }}">
                    <flux:text>{{ \Illuminate\Support\Str::headline($label) }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold">{{ $value }}</div>
                </flux:card>
            @endif
        @endforeach
    </div>

    <section class="space-y-3 mt-8">
        <flux:heading size="lg">Aktivitas Terbaru</flux:heading>
        @forelse ($recentActivities as $activity)
            <flux:card wire:key="activity-{{ $activity->id }}">
                <div class="font-semibold">{{ $activity->student->name ?? 'User' }} menyelesaikan Asesmen: {{ $activity->assessment->title ?? '-' }}</div>
                <flux:text>Skor: {{ $activity->total_score ?? 0 }}/{{ $activity->max_score ?? 0 }} - {{ $activity->status ?? 'unknown' }} ({{ $activity->updated_at->diffForHumans() }})</flux:text>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>Belum ada aktivitas.</flux:text>
            </flux:card>
        @endforelse
    </section>
</div>
