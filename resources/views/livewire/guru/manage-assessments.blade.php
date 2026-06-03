<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Asesmen</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">Kelola Asesmen</flux:heading>
            <flux:text>Susun asesmen formatif dan akhir, publikasikan ke murid, lalu kelola soal dan rubriknya.</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button type="button" :href="route('guru.questions')" wire:navigate>Kelola Soal</flux:button>
            <flux:button type="button" variant="ghost" :href="route('guru.rubrics')" wire:navigate>Rubrik Uraian</flux:button>
            <flux:button type="button" variant="ghost" :href="route('guru.reports')" wire:navigate>Lihat Laporan</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <div class="text-sm text-zinc-500">Total Asesmen</div>
            <div class="mt-1 text-2xl font-semibold">{{ $assessments->count() }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Dipublikasikan</div>
            <div class="mt-1 text-2xl font-semibold">{{ $publishedCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Draft</div>
            <div class="mt-1 text-2xl font-semibold">{{ $draftCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Soal / Attempt</div>
            <div class="mt-1 text-2xl font-semibold">{{ $questionCount }} / {{ $attemptCount }}</div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <flux:card class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editingAssessmentId ? 'Edit Asesmen' : 'Buat Asesmen' }}</flux:heading>
                    <flux:text>{{ $editingAssessmentId ? 'Perbarui pengaturan asesmen yang dipilih.' : 'Buat asesmen baru untuk kegiatan belajar atau asesmen akhir modul.' }}</flux:text>
                </div>
                @if ($editingAssessmentId)
                    <flux:button type="button" size="sm" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                @endif
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Modul</flux:label>
                    <flux:select wire:model.live="module_id">
                        <flux:select.option value="">Pilih modul</flux:select.option>
                        @foreach ($modules as $module)
                            <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="module_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Kegiatan belajar</flux:label>
                    <flux:select wire:model="learning_unit_id">
                        <flux:select.option value="">Asesmen akhir modul</flux:select.option>
                        @foreach ($formLearningUnits as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="learning_unit_id" />
                </flux:field>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Tipe</flux:label>
                        <flux:select wire:model="type">
                            <flux:select.option value="formative">Formatif</flux:select.option>
                            <flux:select.option value="final">Akhir</flux:select.option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Urutan</flux:label>
                        <flux:input type="number" min="1" wire:model="order" />
                        <flux:error name="order" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Judul</flux:label>
                    <flux:input wire:model="title" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea rows="3" wire:model="description" />
                    <flux:error name="description" />
                </flux:field>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>KKTP</flux:label>
                        <flux:input type="number" min="0" max="100" wire:model="kktp" />
                        <flux:error name="kktp" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Maks percobaan</flux:label>
                        <flux:input type="number" min="1" max="10" wire:model="max_attempts" />
                        <flux:error name="max_attempts" />
                    </flux:field>
                </div>

                <flux:checkbox wire:model="is_published" label="Publikasikan ke murid" />

                <flux:button type="submit" variant="primary">
                    {{ $editingAssessmentId ? 'Simpan Perubahan' : 'Simpan Asesmen' }}
                </flux:button>
            </form>
        </flux:card>

        <section class="space-y-3">
            <div>
                <flux:heading size="lg">Daftar Asesmen</flux:heading>
                <flux:text>Gunakan aksi cepat untuk publish, edit, kelola soal, atau hapus asesmen.</flux:text>
            </div>

            @forelse ($assessments as $assessment)
                <flux:card wire:key="assessment-{{ $assessment->id }}" class="space-y-3">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div class="min-w-0 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="font-semibold">{{ $assessment->order }}. {{ $assessment->title }}</div>
                                <flux:badge size="sm" color="{{ $assessment->is_published ? 'green' : 'zinc' }}">
                                    {{ $assessment->is_published ? 'Published' : 'Draft' }}
                                </flux:badge>
                                <flux:badge size="sm" color="{{ $assessment->type === 'final' ? 'blue' : 'yellow' }}">
                                    {{ $assessment->type === 'final' ? 'Akhir' : 'Formatif' }}
                                </flux:badge>
                            </div>
                            <flux:text>{{ $assessment->module->title }} - {{ $assessment->learningUnit?->title ?? 'Asesmen akhir modul' }}</flux:text>
                            @if ($assessment->description)
                                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $assessment->description }}</p>
                            @endif
                            <div class="grid gap-2 text-sm md:grid-cols-4">
                                <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-800">KKTP <b>{{ $assessment->kktp }}</b></div>
                                <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-800">Percobaan <b>{{ $assessment->max_attempts }}</b></div>
                                <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-800">Soal <b>{{ $assessment->questions_count }}</b></div>
                                <div class="rounded-md bg-zinc-50 px-3 py-2 dark:bg-zinc-800">Attempt <b>{{ $assessment->attempts_count }}</b></div>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <flux:button size="sm" variant="ghost" wire:click="togglePublish({{ $assessment->id }})">
                                {{ $assessment->is_published ? 'Unpublish' : 'Publish' }}
                            </flux:button>
                            <flux:button size="sm" wire:click="edit({{ $assessment->id }})">Edit</flux:button>
                            <flux:button size="sm" variant="ghost" :href="route('guru.questions')" wire:navigate>Soal</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $assessment->id }})" wire:confirm="Hapus asesmen ini beserta soal dan attempt murid?">Hapus</flux:button>
                        </div>
                    </div>
                </flux:card>
            @empty
                <flux:card>
                    <flux:text>Belum ada asesmen. Buat asesmen pertama dari form di sebelah kiri.</flux:text>
                </flux:card>
            @endforelse
        </section>
    </div>
</div>
