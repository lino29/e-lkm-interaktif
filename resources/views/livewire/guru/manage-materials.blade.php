<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Materi</flux:heading>
            <flux:text>Tambahkan materi teks, tautan, gambar, video, file, atau simulasi untuk tiap kegiatan belajar.</flux:text>
        </div>
        @if ($editingMaterialId)
            <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal Edit</flux:button>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Kegiatan belajar</flux:label>
            <flux:select wire:model="learning_unit_id">
                <flux:select.option value="">Pilih kegiatan</flux:select.option>
                @foreach ($learningUnits as $unit)
                    <flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="learning_unit_id" />
        </flux:field>

        <flux:field>
            <flux:label>Judul materi</flux:label>
            <flux:input wire:model="title" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>Tipe materi</flux:label>
            <flux:select wire:model="material_type">
                @foreach (['text', 'image', 'video', 'simulation', 'file', 'link'] as $type)
                    <flux:select.option value="{{ $type }}">{{ \Illuminate\Support\Str::headline($type) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="material_type" />
        </flux:field>

        <flux:field>
            <flux:label>Urutan</flux:label>
            <flux:input type="number" min="1" wire:model="order" />
            <flux:error name="order" />
        </flux:field>

        <flux:field class="md:col-span-2">
            <flux:label>Konten atau URL</flux:label>
            <flux:textarea wire:model="content" rows="5" />
            <flux:error name="content" />
        </flux:field>

        <flux:field class="md:col-span-2">
            <flux:label>Upload file materi</flux:label>
            <flux:input type="file" wire:model="file" />
            <flux:error name="file" />
            @if ($existingFilePath)
                <flux:description>File saat ini: {{ basename($existingFilePath) }}</flux:description>
            @endif
        </flux:field>

        <div class="md:col-span-2">
            <flux:button type="submit" variant="primary">
                {{ $editingMaterialId ? 'Perbarui Materi' : 'Simpan Materi' }}
            </flux:button>
        </div>
    </form>

    <div class="space-y-3">
        @foreach ($materials as $material)
            <flux:card wire:key="material-{{ $material->id }}" class="space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-semibold">{{ $material->order }}. {{ $material->title }}</div>
                        <flux:text>{{ $material->learningUnit->title }}. {{ \Illuminate\Support\Str::headline($material->material_type) }}</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="edit({{ $material->id }})">Edit</flux:button>
                        <flux:button size="sm" variant="danger" wire:click="delete({{ $material->id }})" wire:confirm="Hapus materi ini?">Hapus</flux:button>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
