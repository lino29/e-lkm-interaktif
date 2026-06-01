<div class="space-y-4">
    <flux:field>
        <flux:label>Pertanyaan Forum</flux:label>
        <flux:input wire:model.live.blur="form.title" />
        <flux:error name="form.title" />
    </flux:field>

    <flux:field>
        <flux:label>Aturan Forum</flux:label>
        <flux:textarea rows="4" wire:model="form.content" />
        <flux:error name="form.content" />
    </flux:field>

    <div class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Minimal posting</flux:label>
            <flux:input type="number" min="0" wire:model="form.content_json.minimum_posts" />
        </flux:field>

        <flux:field>
            <flux:label>Minimal balasan</flux:label>
            <flux:input type="number" min="0" wire:model="form.content_json.minimum_replies" />
        </flux:field>
    </div>
</div>
