<div class="space-y-6">
    <flux:heading size="xl">Modul E-LKM</flux:heading>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($modules as $module)
            <flux:card wire:key="my-module-{{ $module->id }}">
                <div class="font-semibold">{{ $module->title }}</div>
                <flux:text>{{ $module->subject->name }} · {{ $module->learningUnits->count() }} kegiatan · KKTP {{ $module->kktp }}</flux:text>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($module->learningUnits as $unit)
                        <flux:button size="sm" :href="route('murid.learning-units.show', $unit)" wire:navigate>{{ $unit->order }}. {{ \Illuminate\Support\Str::limit($unit->title, 28) }}</flux:button>
                    @endforeach
                    @foreach ($module->assessments as $assessment)
                        <flux:button size="sm" variant="primary" :href="route('murid.assessments.show', $assessment)" wire:navigate>{{ $assessment->title }}</flux:button>
                    @endforeach
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
