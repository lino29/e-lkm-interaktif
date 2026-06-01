<div class="space-y-4">
    <flux:field>
        <flux:label>{{ $form['section_type'] === 'learning_objective' ? 'Judul Tujuan Pembelajaran' : 'Judul' }}</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    <flux:field>
        <flux:label>{{ $form['section_type'] === 'learning_objective' ? 'Isi Tujuan Pembelajaran' : 'Isi Konten' }}</flux:label>
        <x-forms.rich-editor wire:model.live="form.content" id="section-content-editor-{{ $selectedSection->id }}" />
        <flux:error name="form.content" />
    </flux:field>
</div>
