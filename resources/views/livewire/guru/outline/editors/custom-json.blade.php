<div class="space-y-4">
    <flux:field>
        <flux:label>Judul</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    <flux:field>
        <flux:label>Custom JSON</flux:label>
        <flux:textarea rows="8" wire:model="contentJsonText" />
        <flux:error name="contentJsonText" />
    </flux:field>
</div>
