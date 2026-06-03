<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('guru.assessments') }}">Asesmen</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Rubrik</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">Kelola Rubrik Uraian</flux:heading>
            <flux:text>Rubrik dipakai untuk membantu penilaian otomatis dan review soal uraian.</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button type="button" :href="route('guru.questions')" wire:navigate>Kelola Soal</flux:button>
            <flux:button type="button" variant="ghost" :href="route('guru.assessments')" wire:navigate>Kembali ke Asesmen</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <flux:card class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editingRubricId ? 'Edit Rubrik' : 'Tambah Rubrik' }}</flux:heading>
                    <flux:text>Tentukan kriteria, level, deskripsi, dan skor maksimum per kriteria.</flux:text>
                </div>
                @if ($editingRubricId)
                    <flux:button type="button" size="sm" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                @endif
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Soal uraian</flux:label>
                    <flux:select wire:model="question_id">
                        <flux:select.option value="">Pilih soal</flux:select.option>
                        @foreach ($questions as $question)
                            <flux:select.option value="{{ $question->id }}">{{ \Illuminate\Support\Str::limit($question->question_text, 80) }} - {{ $question->assessment->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="question_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Kriteria</flux:label>
                    <flux:input wire:model="criterion" />
                    <flux:error name="criterion" />
                </flux:field>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Level</flux:label>
                        <flux:input wire:model="level" placeholder="Baik / Cukup / Perlu bimbingan" />
                        <flux:error name="level" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Skor</flux:label>
                        <flux:input type="number" step="0.01" min="0" max="100" wire:model="score" />
                        <flux:error name="score" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea rows="4" wire:model="description" />
                    <flux:error name="description" />
                </flux:field>

                <flux:button type="submit" variant="primary">
                    {{ $editingRubricId ? 'Simpan Perubahan' : 'Simpan Rubrik' }}
                </flux:button>
            </form>
        </flux:card>

        <section class="space-y-3">
            <div>
                <flux:heading size="lg">Daftar Rubrik</flux:heading>
                <flux:text>{{ $rubrics->count() }} kriteria rubrik tersedia.</flux:text>
            </div>

            @forelse ($rubrics as $rubric)
                <flux:card wire:key="rubric-{{ $rubric->id }}" class="space-y-2">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="font-semibold">{{ $rubric->criterion }}</div>
                                <flux:badge size="sm" color="blue">{{ $rubric->score }}</flux:badge>
                                @if ($rubric->level)
                                    <flux:badge size="sm">{{ $rubric->level }}</flux:badge>
                                @endif
                            </div>
                            <flux:text>{{ $rubric->question->assessment->title }} - {{ \Illuminate\Support\Str::limit($rubric->question->question_text, 120) }}</flux:text>
                            @if ($rubric->description)
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $rubric->description }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <flux:button type="button" size="sm" wire:click="edit({{ $rubric->id }})">Edit</flux:button>
                            <flux:button type="button" size="sm" variant="danger" wire:click="delete({{ $rubric->id }})" wire:confirm="Hapus rubrik ini?">Hapus</flux:button>
                        </div>
                    </div>
                </flux:card>
            @empty
                <flux:card>
                    <flux:text>Belum ada rubrik. Tambahkan rubrik untuk soal uraian.</flux:text>
                </flux:card>
            @endforelse
        </section>
    </div>
</div>
