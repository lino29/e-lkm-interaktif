<flux:field>
    <flux:label>Jawaban</flux:label>
    <flux:input wire:model="answer_text" :disabled="$answer?->status === 'reviewed'" />
    <flux:error name="answer_text" />
</flux:field>
