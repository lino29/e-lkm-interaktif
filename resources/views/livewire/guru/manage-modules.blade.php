<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Modul</flux:heading>
            <flux:text>Susun pendahuluan, tujuan, cover, KKTP, dan status publikasi modul E-LKM.</flux:text>
        </div>
        @if ($editingModuleId)
            <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal Edit</flux:button>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Mata pelajaran</flux:label>
            <flux:select wire:model="subject_id">
                <flux:select.option value="">Pilih mata pelajaran</flux:select.option>
                @foreach ($subjects as $subject)
                    <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="subject_id" />
        </flux:field>

        <flux:field>
            <flux:label>Judul modul</flux:label>
            <flux:input wire:model="title" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>Status</flux:label>
            <flux:select wire:model="status">
                <flux:select.option value="draft">Draft</flux:select.option>
                <flux:select.option value="published">Published</flux:select.option>
            </flux:select>
            <flux:error name="status" />
        </flux:field>

        <flux:field>
            <flux:label>Cover modul</flux:label>
            <flux:input type="file" wire:model="cover" accept="image/*" />
            <flux:error name="cover" />
            @if ($existingCoverPath)
                <flux:description>Cover saat ini: {{ basename($existingCoverPath) }}</flux:description>
            @endif
        </flux:field>

        <flux:field>
            <flux:label>KKTP</flux:label>
            <flux:input type="number" min="0" max="100" wire:model="kktp" />
            <flux:error name="kktp" />
        </flux:field>

        <flux:field>
            <flux:label>Maks percobaan asesmen</flux:label>
            <flux:input type="number" min="1" max="10" wire:model="max_attempts" />
            <flux:error name="max_attempts" />
        </flux:field>

        <flux:field>
            <flux:label>Pendahuluan</flux:label>
            <flux:textarea wire:model="introduction" rows="5" />
            <flux:error name="introduction" />
        </flux:field>

        <flux:field>
            <flux:label>Tujuan pembelajaran</flux:label>
            <flux:textarea wire:model="learning_objectives" rows="5" />
            <flux:error name="learning_objectives" />
        </flux:field>

        <div class="md:col-span-2">
            <flux:button type="submit" variant="primary">
                {{ $editingModuleId ? 'Perbarui Modul' : 'Simpan Modul' }}
            </flux:button>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($modules as $module)
            <flux:card wire:key="module-{{ $module->id }}" class="space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-semibold">{{ $module->title }}</div>
                        <flux:text>{{ $module->subject->name }}. {{ $module->status }}. KKTP {{ $module->kktp }}. {{ $module->learning_units_count }} kegiatan. {{ $module->assessments_count }} asesmen.</flux:text>
                    </div>
                    <flux:badge color="{{ $module->status === 'published' ? 'green' : 'zinc' }}">{{ $module->status }}</flux:badge>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button size="sm" :href="route('guru.modules.show', $module)" wire:navigate>Detail</flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="edit({{ $module->id }})">Edit</flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="toggleStatus({{ $module->id }})">
                        {{ $module->status === 'published' ? 'Jadikan Draft' : 'Publish' }}
                    </flux:button>
                    <flux:button size="sm" variant="danger" wire:click="delete({{ $module->id }})" wire:confirm="Hapus modul beserta kegiatan, materi, aktivitas, dan asesmennya?">Hapus</flux:button>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
