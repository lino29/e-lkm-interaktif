<div class="space-y-6">
    <flux:heading size="xl">Kelola Materi</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Kegiatan</flux:label><flux:select wire:model="learning_unit_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($learningUnits as $unit)<flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Judul</flux:label><flux:input wire:model="title" /></flux:field>
        <flux:field><flux:label>Tipe</flux:label><flux:input wire:model="material_type" /></flux:field>
        <flux:field><flux:label>Urutan</flux:label><flux:input type="number" wire:model="order" /></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Konten</flux:label><flux:textarea wire:model="content" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Materi</flux:button>
    </form>
    @foreach ($materials as $material)
        <flux:card wire:key="material-{{ $material->id }}"><div class="font-semibold">{{ $material->title }}</div><flux:text>{{ $material->learningUnit->title }}</flux:text></flux:card>
    @endforeach
</div>
