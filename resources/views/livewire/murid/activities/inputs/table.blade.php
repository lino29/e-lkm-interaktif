@php
    $columns = $currentActivity->answer_schema['columns'] ?? [];
    $allowAdd = $currentActivity->answer_schema['allow_add'] ?? true;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
        <thead class="bg-zinc-50 text-xs uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            <tr>
                @foreach ($columns as $column)
                    <th class="px-4 py-3">{{ $column['label'] ?? $column['name'] }}</th>
                @endforeach
                @if (! ($answer?->status === 'reviewed'))
                    <th class="px-4 py-3 text-right">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($answer_json as $rowIndex => $row)
                <tr class="border-b bg-white dark:border-zinc-700 dark:bg-zinc-900" wire:key="activity-table-row-{{ $rowIndex }}">
                    @foreach ($columns as $column)
                        <td class="px-4 py-2">
                            @switch($column['type'] ?? 'text')
                                @case('select')
                                    <flux:select wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}" :disabled="$answer?->status === 'reviewed'">
                                        <flux:select.option value="">Pilih</flux:select.option>
                                        @foreach (($column['options'] ?? []) as $option)
                                            <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @break

                                @case('textarea')
                                    <flux:textarea wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}" :disabled="$answer?->status === 'reviewed'" />
                                    @break

                                @case('number')
                                    <flux:input type="number" wire:model.live="answer_json.{{ $rowIndex }}.{{ $column['name'] }}" :disabled="$answer?->status === 'reviewed'" />
                                    @break

                                @case('readonly_text')
                                    <flux:input value="{{ $answer_json[$rowIndex][$column['name']] ?? '' }}" disabled />
                                    @break

                                @case('computed')
                                    <flux:input value="{{ $this->calculateComputedValue($column['formula'] ?? null, $answer_json[$rowIndex] ?? []) }}" disabled />
                                    @break

                                @default
                                    <flux:input wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}" :disabled="$answer?->status === 'reviewed'" />
                            @endswitch
                        </td>
                    @endforeach
                    @if (! ($answer?->status === 'reviewed'))
                        <td class="px-4 py-2 text-right">
                            <flux:button type="button" size="sm" variant="danger" wire:click="removeTableRow({{ $rowIndex }})">Hapus</flux:button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($allowAdd && ! ($answer?->status === 'reviewed'))
    <flux:button type="button" size="sm" wire:click="addTableRow" class="mt-2">Tambah Baris</flux:button>
@endif

<flux:error name="answer_json" />
