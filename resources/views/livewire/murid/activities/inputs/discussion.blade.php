<flux:field>
    <flux:label>Refleksi/Diskusi</flux:label>
    <flux:textarea wire:model="answer_text" rows="6" :disabled="$answer?->status === 'reviewed'" />
    <flux:error name="answer_text" />
</flux:field>
