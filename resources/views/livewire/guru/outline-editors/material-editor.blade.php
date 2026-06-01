<flux:field>
    <flux:label>Uraian Materi</flux:label>
    <x-forms.rich-editor wire:model="form.content" id="material-content-editor-{{ $selectedSection->id }}" />
    <flux:error name="form.content" />
</flux:field>
