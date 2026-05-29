<div class="space-y-6">
    <flux:heading size="xl">Proyek Saya</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Modul</flux:label><flux:select wire:model="module_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($modules as $module)<flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Judul proyek</flux:label><flux:input wire:model="project_title" /></flux:field>
        <flux:field><flux:label>Masalah</flux:label><flux:textarea wire:model="problem" /></flux:field>
        <flux:field><flux:label>Tujuan</flux:label><flux:textarea wire:model="objective" /></flux:field>
        <flux:field><flux:label>Prosedur</flux:label><flux:textarea wire:model="procedure" /></flux:field>
        <flux:field><flux:label>Kesimpulan</flux:label><flux:textarea wire:model="conclusion" /></flux:field>
        <flux:button type="submit" variant="primary">Kirim Proyek</flux:button>
    </form>
    @foreach ($projects as $project)
        <flux:card wire:key="my-project-{{ $project->id }}"><div class="font-semibold">{{ $project->project_title }}</div><flux:text>{{ $project->module->title }} · {{ $project->status }} · Nilai {{ $project->score ?? '-' }}</flux:text></flux:card>
    @endforeach
</div>
