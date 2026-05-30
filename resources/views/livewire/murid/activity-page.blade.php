<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $activity->title }}</flux:heading>
        <flux:text>{{ $activity->learningUnit->title }} · {{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text>
    </div>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <flux:card>
        <p class="text-sm leading-6">{{ $activity->prompt }}</p>
    </flux:card>
    <form wire:submit="submit" class="space-y-4">
        <flux:field>
            <flux:label>Jawaban</flux:label>
            <flux:textarea wire:model="answer_text" />
            <flux:error name="answer_text" />
        </flux:field>
        @if ($activity->input_type === 'table')
            <flux:field>
                <flux:label>Jawaban tabel JSON</flux:label>
                <flux:textarea wire:model="answer_json_text" placeholder='[{"alat":"Lampu","energi_masuk":"Listrik"}]' />
                <flux:error name="answer_json_text" />
            </flux:field>
        @endif
        @if ($activity->input_type === 'file')
            <flux:field>
                <flux:label>Upload file</flux:label>
                <flux:input type="file" wire:model="file" />
                <flux:error name="file" />
            </flux:field>
        @endif
        <flux:button type="submit" variant="primary">Kirim Jawaban</flux:button>
    </form>
</div>
