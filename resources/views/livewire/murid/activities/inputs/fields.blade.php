<div class="grid gap-4">
    @foreach (($currentActivity->answer_schema['fields'] ?? []) as $field)
        <flux:field wire:key="activity-field-{{ $field['name'] }}">
            <flux:label>{{ $field['label'] ?? \Illuminate\Support\Str::headline($field['name']) }}</flux:label>

            @if (($field['type'] ?? 'text') === 'textarea')
                <flux:textarea wire:model="field_data.{{ $field['name'] }}" rows="4" :disabled="$answer?->status === 'reviewed'" />
            @elseif (($field['type'] ?? 'text') === 'select')
                <flux:select wire:model="field_data.{{ $field['name'] }}" :disabled="$answer?->status === 'reviewed'">
                    <flux:select.option value="">Pilih</flux:select.option>
                    @foreach (($field['options'] ?? []) as $option)
                        <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                    @endforeach
                </flux:select>
            @elseif (($field['type'] ?? 'text') === 'number')
                <flux:input type="number" wire:model="field_data.{{ $field['name'] }}" :disabled="$answer?->status === 'reviewed'" />
            @elseif (($field['type'] ?? 'text') === 'readonly_text')
                <flux:input value="{{ $field_data[$field['name']] ?? $field['value'] ?? '' }}" disabled />
            @else
                <flux:input wire:model="field_data.{{ $field['name'] }}" :disabled="$answer?->status === 'reviewed'" />
            @endif

            <flux:error name="field_data.{{ $field['name'] }}" />
        </flux:field>
    @endforeach
</div>
