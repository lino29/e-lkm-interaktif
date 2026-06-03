<div class="grid gap-4 md:grid-cols-[1fr_120px]">
    <flux:field>
        <flux:label>Judul section</flux:label>
        <flux:input wire:model="titles.{{ $section->id }}" />
        <flux:error name="titles.{{ $section->id }}" />
    </flux:field>

    <flux:field>
        <flux:label>Urutan</flux:label>
        <flux:input type="number" min="1" wire:model="orders.{{ $section->id }}" />
    </flux:field>
</div>

@if (
    in_array($section->editor_type, ['rich_text', 'material_editor'], true)
        || in_array($section->section_type, ['learning_objective', 'material_item', 'custom_content'], true)
)
    <flux:field>
        <flux:label>Konten</flux:label>
        <x-forms.rich-editor wire:model="contents.{{ $section->id }}" id="section-content-{{ $section->id }}" />
    </flux:field>
@endif

@if ($section->section_type === 'key_points')
    <flux:field>
        <flux:label>Pokok-pokok materi (JSON)</flux:label>
        <flux:textarea rows="7" wire:model="contentJson.{{ $section->id }}" />
        <flux:error name="contentJson.{{ $section->id }}" />
    </flux:field>
@endif

<div class="grid gap-4 md:grid-cols-[160px_1fr_auto]">
    <flux:field>
        <flux:label>Link model</flux:label>
        <flux:select wire:model="linkTypes.{{ $section->id }}">
            <flux:select.option value="">Tidak ada</flux:select.option>
            <flux:select.option value="material">Materi</flux:select.option>
            <flux:select.option value="activity">Aktivitas</flux:select.option>
            <flux:select.option value="assessment">Asesmen</flux:select.option>
        </flux:select>
    </flux:field>

    <flux:field>
        <flux:label>Target</flux:label>
        <flux:select wire:model="linkIds.{{ $section->id }}">
            <flux:select.option value="">Pilih target</flux:select.option>
            @foreach ($materials as $material)
                <flux:select.option value="{{ $material->id }}">Materi: {{ $material->title }}</flux:select.option>
            @endforeach
            @foreach ($activities as $activity)
                <flux:select.option value="{{ $activity->id }}">Aktivitas: {{ $activity->title }}</flux:select.option>
            @endforeach
            @foreach ($assessments as $assessment)
                <flux:select.option value="{{ $assessment->id }}">Asesmen: {{ $assessment->title }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:field>

    <div class="flex flex-wrap items-end gap-2">
        <flux:button type="button" size="sm" wire:click="saveSection({{ $section->id }})">Simpan</flux:button>
        <flux:button type="button" size="sm" variant="ghost" wire:click="linkSection({{ $section->id }})">Link</flux:button>
        <flux:button type="button" size="sm" variant="ghost" wire:click="moveSection({{ $section->id }}, 'up')">Naik</flux:button>
        <flux:button type="button" size="sm" variant="ghost" wire:click="moveSection({{ $section->id }}, 'down')">Turun</flux:button>
    </div>
</div>
