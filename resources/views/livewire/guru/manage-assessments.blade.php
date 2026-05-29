<div class="space-y-6">
    <flux:heading size="xl">Kelola Asesmen</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Modul</flux:label><flux:select wire:model="module_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($modules as $module)<flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Kegiatan</flux:label><flux:select wire:model="learning_unit_id"><flux:select.option value="">Asesmen akhir</flux:select.option>@foreach ($learningUnits as $unit)<flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Judul</flux:label><flux:input wire:model="title" /></flux:field>
        <flux:field><flux:label>Tipe</flux:label><flux:select wire:model="type"><flux:select.option value="formative">Formatif</flux:select.option><flux:select.option value="final">Akhir</flux:select.option></flux:select></flux:field>
        <flux:field><flux:label>KKTP</flux:label><flux:input type="number" wire:model="kktp" /></flux:field>
        <flux:field><flux:label>Maks percobaan</flux:label><flux:input type="number" wire:model="max_attempts" /></flux:field>
        <flux:field><flux:checkbox wire:model="is_published" label="Publikasikan" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Asesmen</flux:button>
    </form>
    @foreach ($assessments as $assessment)
        <flux:card wire:key="assessment-{{ $assessment->id }}"><div class="font-semibold">{{ $assessment->title }}</div><flux:text>{{ $assessment->module->title }} · KKTP {{ $assessment->kktp }}</flux:text></flux:card>
    @endforeach
</div>
