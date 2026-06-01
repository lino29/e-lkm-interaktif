<div class="space-y-4">
    <flux:field>
        <flux:label>{{ $form['section_type'] === 'material_group' ? 'Judul Uraian Materi' : 'Judul Submateri' }}</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    @if ($form['section_type'] === 'material_item')
        <flux:field>
            <flux:label>Hubungkan ke Materi yang Sudah Ada</flux:label>
            <flux:select wire:model="form.linked_model_id">
                <flux:select.option value="">Tidak dihubungkan</flux:select.option>
                @foreach ($materials as $material)
                    <flux:select.option value="{{ $material->id }}">{{ $material->title }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="form.linked_model_id" />
        </flux:field>

        <flux:field>
            <flux:label>Isi Materi</flux:label>
            <x-forms.rich-editor wire:model.live="form.content" id="material-content-editor-{{ $selectedSection->id }}" />
            <flux:error name="form.content" />
        </flux:field>
    @else
        <flux:field>
            <flux:label>Deskripsi Singkat</flux:label>
            <flux:textarea rows="3" wire:model="form.content" />
            <flux:error name="form.content" />
        </flux:field>
    @endif
</div>
