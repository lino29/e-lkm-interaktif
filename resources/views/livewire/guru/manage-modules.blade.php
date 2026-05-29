<div class="space-y-6">
    <flux:heading size="xl">Kelola Modul</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Mata pelajaran</flux:label><flux:select wire:model="subject_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($subjects as $subject)<flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>@endforeach</flux:select><flux:error name="subject_id" /></flux:field>
        <flux:field><flux:label>Judul modul</flux:label><flux:input wire:model="title" /><flux:error name="title" /></flux:field>
        <flux:field><flux:label>Status</flux:label><flux:select wire:model="status"><flux:select.option value="draft">Draft</flux:select.option><flux:select.option value="published">Published</flux:select.option></flux:select></flux:field>
        <flux:field><flux:label>KKTP</flux:label><flux:input type="number" wire:model="kktp" /></flux:field>
        <flux:field><flux:label>Maks percobaan</flux:label><flux:input type="number" wire:model="max_attempts" /></flux:field>
        <flux:field><flux:label>Pendahuluan</flux:label><flux:textarea wire:model="introduction" /></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Tujuan pembelajaran</flux:label><flux:textarea wire:model="learning_objectives" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Modul</flux:button>
    </form>
    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($modules as $module)
            <flux:card wire:key="module-{{ $module->id }}">
                <div class="font-semibold">{{ $module->title }}</div>
                <flux:text>{{ $module->subject->name }} · {{ $module->status }} · KKTP {{ $module->kktp }}</flux:text>
            </flux:card>
        @endforeach
    </div>
</div>
