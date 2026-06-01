<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div>
            <flux:heading size="xl">{{ $module->title }}</flux:heading>
            <flux:text>{{ $module->subject->name }}. KKTP {{ $module->kktp }}. {{ $module->learningUnits->count() }} kegiatan belajar.</flux:text>
            <div class="mt-3 max-w-sm">
                <div class="mb-1 flex justify-between text-sm text-zinc-600 dark:text-zinc-300">
                    <span>Progress modul</span>
                    <span>{{ $moduleProgressPercentage }}%</span>
                </div>
                <flux:progress :value="$moduleProgressPercentage" />
            </div>
        </div>
        <flux:button :href="route('murid.modules')" wire:navigate>Kembali ke Modul</flux:button>
    </div>

    <section class="space-y-4">
        <flux:heading>Pendahuluan Modul</flux:heading>
        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($module->sections->where('section_type', 'introduction')->where('is_visible', true) as $section)
                <flux:card wire:key="module-introduction-{{ $section->id }}">
                    <flux:heading size="lg">{{ $section->title }}</flux:heading>
                    <div class="ck-content learning-content prose mt-3 max-w-none text-sm dark:prose-invert">{!! $section->content !!}</div>
                </flux:card>
            @empty
                <flux:card>
                    <flux:heading>Pendahuluan</flux:heading>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $module->introduction ?: 'Pendahuluan belum tersedia.' }}</p>
                </flux:card>
            @endforelse
        </div>
    </section>

    <section class="space-y-4">
        <flux:heading>Kegiatan Belajar</flux:heading>
        @foreach ($module->learningUnits as $unit)
            @php
                $isUnlocked = in_array($unit->id, $unlockedUnitIds, true);
                $isCompleted = in_array($unit->id, $completedUnitIds, true);
            @endphp
            <flux:card wire:key="murid-module-unit-{{ $unit->id }}" class="space-y-4">
                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold">{{ $unit->order }}. {{ $unit->title }}</h2>
                            @if ($isCompleted)
                                <flux:badge color="green" size="sm">Tuntas</flux:badge>
                            @elseif (! $isUnlocked)
                                <flux:badge color="zinc" size="sm">Terkunci</flux:badge>
                            @endif
                        </div>
                        <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $unit->objectives ?: $unit->description }}</p>
                    </div>
                    @if ($isUnlocked)
                        <flux:button size="sm" :href="route('murid.learning-units.show', $unit)" wire:navigate>Buka Kegiatan</flux:button>
                    @else
                        <flux:button size="sm" disabled>Selesaikan KB Sebelumnya</flux:button>
                    @endif
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">{{ $unit->materials->count() }} materi</div>
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">{{ $unit->activities->count() }} aktivitas</div>
                    <div class="rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">{{ $unit->assessments->count() }} asesmen</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($unit->activities as $activity)
                        @if ($isUnlocked && in_array($activity->id, $activityUnlockedIds, true))
                            <flux:button size="sm" :href="route('murid.activities.show', $activity)" wire:navigate>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:button>
                        @else
                            <flux:button size="sm" disabled>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:button>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        @endforeach
    </section>

    <section class="space-y-4">
        <flux:heading>Asesmen Modul</flux:heading>
        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($module->assessments as $assessment)
                <flux:card wire:key="murid-module-assessment-{{ $assessment->id }}">
                    <div class="font-semibold">{{ $assessment->title }}</div>
                    <flux:text>KKTP {{ $assessment->kktp }}. Maks {{ $assessment->max_attempts }} percobaan.</flux:text>
                    @if ($allUnitsCompleted)
                        <flux:button class="mt-3" size="sm" variant="primary" :href="route('murid.assessments.show', $assessment)" wire:navigate>Kerjakan</flux:button>
                    @else
                        <flux:button class="mt-3" size="sm" variant="primary" disabled>Selesaikan semua KB</flux:button>
                    @endif
                </flux:card>
            @empty
                <flux:text>Belum ada asesmen modul.</flux:text>
            @endforelse
        </div>
    </section>

    @if ($module->glossaries->isNotEmpty())
        <section class="space-y-4">
            <flux:heading>Glosarium</flux:heading>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($module->glossaries as $glossary)
                    <flux:card wire:key="glossary-{{ $glossary->id }}">
                        <div class="font-semibold">{{ $glossary->term }}</div>
                        <flux:text>{{ $glossary->definition }}</flux:text>
                    </flux:card>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <flux:heading>Penutup Modul</flux:heading>
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ($module->sections->where('section_type', 'closing')->where('is_visible', true) as $section)
                <flux:card wire:key="module-closing-{{ $section->id }}">
                    <flux:heading size="lg">{{ $section->title }}</flux:heading>
                    <div class="ck-content learning-content prose mt-3 max-w-none text-sm dark:prose-invert">{!! $section->content !!}</div>
                </flux:card>
            @endforeach
        </div>
    </section>

    @if ($module->references->isNotEmpty())
        <section class="space-y-4">
            <flux:heading>Referensi</flux:heading>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($module->references as $reference)
                    <flux:card wire:key="reference-{{ $reference->id }}">
                        <div class="font-semibold">{{ $reference->title }}</div>
                        <flux:text>{{ $reference->author }} ({{ $reference->year }}). {{ $reference->source }}</flux:text>
                    </flux:card>
                @endforeach
            </div>
        </section>
    @endif
</div>
