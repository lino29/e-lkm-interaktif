<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div>
            <flux:heading size="xl">{{ $module->title }}</flux:heading>
            <flux:text>{{ $module->subject->name }}. Status {{ $module->status }}. KKTP {{ $module->kktp }}. Maks {{ $module->max_attempts }} percobaan.</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('guru.modules')" wire:navigate>Kembali</flux:button>
            <flux:button variant="primary" wire:click="toggleStatus">
                {{ $module->status === 'published' ? 'Jadikan Draft' : 'Publish Modul' }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <flux:text>Kegiatan Belajar</flux:text>
            <div class="mt-2 text-3xl font-semibold">{{ $module->learningUnits->count() }}</div>
        </flux:card>
        <flux:card>
            <flux:text>Materi</flux:text>
            <div class="mt-2 text-3xl font-semibold">{{ $module->learningUnits->sum(fn ($unit) => $unit->materials->count()) }}</div>
        </flux:card>
        <flux:card>
            <flux:text>Aktivitas</flux:text>
            <div class="mt-2 text-3xl font-semibold">{{ $module->learningUnits->sum(fn ($unit) => $unit->activities->count()) }}</div>
        </flux:card>
    </div>

    <section class="grid gap-4 lg:grid-cols-2">
        <flux:card>
            <flux:heading>Pendahuluan</flux:heading>
            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $module->introduction ?: 'Belum ada pendahuluan.' }}</p>
        </flux:card>
        <flux:card>
            <flux:heading>Tujuan Pembelajaran</flux:heading>
            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $module->learning_objectives ?: 'Belum ada tujuan pembelajaran.' }}</p>
        </flux:card>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading>Kegiatan Belajar</flux:heading>
            <div class="flex gap-2">
                <flux:button size="sm" :href="route('guru.learning-units')" wire:navigate>Tambah Kegiatan</flux:button>
                <flux:button size="sm" :href="route('guru.materials')" wire:navigate>Tambah Materi</flux:button>
                <flux:button size="sm" :href="route('guru.activities')" wire:navigate>Tambah Aktivitas</flux:button>
            </div>
        </div>

        @forelse ($module->learningUnits as $unit)
            <flux:card wire:key="module-detail-unit-{{ $unit->id }}" class="space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold">{{ $unit->order }}. {{ $unit->title }}</h3>
                        <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $unit->objectives ?: $unit->description }}</p>
                    </div>
                    <flux:button size="sm" variant="danger" wire:click="deleteLearningUnit({{ $unit->id }})" wire:confirm="Hapus kegiatan belajar ini?">Hapus</flux:button>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        <div class="text-sm font-medium">Materi</div>
                        @forelse ($unit->materials as $material)
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800" wire:key="unit-material-{{ $material->id }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-medium">{{ $material->order }}. {{ $material->title }}</div>
                                        <div class="text-xs text-zinc-500">{{ \Illuminate\Support\Str::headline($material->material_type) }}</div>
                                    </div>
                                    <flux:button size="sm" variant="danger" wire:click="deleteMaterial({{ $material->id }})" wire:confirm="Hapus materi ini?">Hapus</flux:button>
                                </div>
                            </div>
                        @empty
                            <flux:text>Belum ada materi.</flux:text>
                        @endforelse
                    </div>

                    <div class="space-y-3">
                        <div class="text-sm font-medium">Aktivitas</div>
                        @forelse ($unit->activities as $activity)
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800" wire:key="unit-activity-{{ $activity->id }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-medium">{{ $activity->order }}. {{ $activity->title }}</div>
                                        <div class="text-xs text-zinc-500">{{ \Illuminate\Support\Str::headline($activity->phase) }}. {{ $activity->answers->count() }} jawaban</div>
                                    </div>
                                    <flux:button size="sm" variant="danger" wire:click="deleteActivity({{ $activity->id }})" wire:confirm="Hapus aktivitas ini?">Hapus</flux:button>
                                </div>
                            </div>
                        @empty
                            <flux:text>Belum ada aktivitas.</flux:text>
                        @endforelse
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>Modul belum memiliki kegiatan belajar.</flux:text>
            </flux:card>
        @endforelse
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <flux:card>
            <flux:heading>Asesmen</flux:heading>
            <div class="mt-3 space-y-2">
                @forelse ($module->assessments as $assessment)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        {{ $assessment->title }}. {{ $assessment->questions->count() }} soal. KKTP {{ $assessment->kktp }}
                    </div>
                @empty
                    <flux:text>Belum ada asesmen.</flux:text>
                @endforelse
            </div>
        </flux:card>

        <flux:card>
            <flux:heading>Glosarium & Referensi</flux:heading>
            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                Glosarium: {{ $module->glossaries->count() }} item. Referensi: {{ $module->references->count() }} item.
            </p>
        </flux:card>
    </section>
</div>
