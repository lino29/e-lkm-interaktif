<div class="space-y-6">
    <flux:heading size="xl">Kelola Kegiatan Belajar</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Modul</flux:label><flux:select wire:model="module_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($modules as $module)<flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>@endforeach</flux:select><flux:error name="module_id" /></flux:field>
        <flux:field><flux:label>Judul</flux:label><flux:input wire:model="title" /><flux:error name="title" /></flux:field>
        <flux:field><flux:label>Urutan</flux:label><flux:input type="number" wire:model="order" /></flux:field>
        <flux:field><flux:label>Tujuan</flux:label><flux:textarea wire:model="objectives" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Kegiatan</flux:button>
    </form>
    @foreach ($learningUnits as $unit)
        <flux:card wire:key="unit-{{ $unit->id }}"><div class="font-semibold">{{ $unit->order }}. {{ $unit->title }}</div><flux:text>{{ $unit->module->title }}</flux:text></flux:card>
    @endforeach
</div>
