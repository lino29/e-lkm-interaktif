<flux:field>
    <flux:label>Konten</flux:label>
    <x-forms.rich-editor wire:model.live="form.content" id="section-content-editor-{{ $selectedSection->id }}" />
    <flux:error name="form.content" />
</flux:field>
