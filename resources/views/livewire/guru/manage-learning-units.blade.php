<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Kegiatan Belajar</flux:heading>
            <flux:text>Atur lima kegiatan belajar utama sesuai struktur E-LKM.</flux:text>
        </div>
        @if ($editingLearningUnitId)
            <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal Edit</flux:button>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Modul</flux:label>
            <flux:select wire:model="module_id">
                <flux:select.option value="">Pilih modul</flux:select.option>
                @foreach ($modules as $module)
                    <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="module_id" />
        </flux:field>

        <flux:field>
            <flux:label>Judul kegiatan</flux:label>
            <flux:input wire:model="title" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>Urutan</flux:label>
            <flux:input type="number" min="1" wire:model="order" />
            <flux:error name="order" />
        </flux:field>

        <flux:field>
            <flux:label>Deskripsi</flux:label>
            <flux:textarea wire:model="description" rows="4" />
            <flux:error name="description" />
        </flux:field>

        <flux:field class="md:col-span-2">
            <flux:label>Tujuan pembelajaran</flux:label>
            <flux:textarea wire:model="objectives" rows="4" />
            <flux:error name="objectives" />
        </flux:field>

        <div class="md:col-span-2">
            <flux:button type="submit" variant="primary">
                {{ $editingLearningUnitId ? 'Perbarui Kegiatan' : 'Simpan Kegiatan' }}
            </flux:button>
        </div>
    </form>

    <div class="space-y-3">
        @foreach ($learningUnits as $unit)
            <flux:card wire:key="unit-{{ $unit->id }}" class="space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-semibold">{{ $unit->order }}. {{ $unit->title }}</div>
                        <flux:text>{{ $unit->module->title }}. {{ $unit->materials_count }} materi. {{ $unit->activities_count }} aktivitas. {{ $unit->assessments_count }} asesmen.</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="edit({{ $unit->id }})">Edit</flux:button>
                        <flux:button size="sm" variant="danger" wire:click="delete({{ $unit->id }})" wire:confirm="Hapus kegiatan belajar ini?">Hapus</flux:button>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
