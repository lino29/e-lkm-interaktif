<div class="space-y-6">
    <flux:heading size="xl">Kelola Aktivitas</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Kegiatan</flux:label><flux:select wire:model="learning_unit_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($learningUnits as $unit)<flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Judul</flux:label><flux:input wire:model="title" /></flux:field>
        <flux:field><flux:label>Fase</flux:label><flux:select wire:model="phase">@foreach (['ayo_mengamati','ayo_bertanya','ayo_mencoba','ayo_menalar','ayo_menyimpulkan','forum_diskusi'] as $phaseOption)<flux:select.option value="{{ $phaseOption }}">{{ \Illuminate\Support\Str::headline($phaseOption) }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Input</flux:label><flux:select wire:model="input_type">@foreach (['short_text','essay','table','file','discussion'] as $inputOption)<flux:select.option value="{{ $inputOption }}">{{ \Illuminate\Support\Str::headline($inputOption) }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Urutan</flux:label><flux:input type="number" wire:model="order" /></flux:field>
        <flux:field><flux:label>Instruksi</flux:label><flux:textarea wire:model="prompt" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Aktivitas</flux:button>
    </form>
    @foreach ($activities as $activity)
        <flux:card wire:key="activity-{{ $activity->id }}"><div class="font-semibold">{{ $activity->title }}</div><flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }} · {{ $activity->learningUnit->title }}</flux:text></flux:card>
    @endforeach
</div>
