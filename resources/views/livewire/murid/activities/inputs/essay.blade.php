<flux:field>
    <flux:label>Jawaban</flux:label>
    <flux:textarea wire:model="answer_text" rows="6" :disabled="$answer?->status === 'reviewed'" />
    <flux:error name="answer_text" />
</flux:field>
