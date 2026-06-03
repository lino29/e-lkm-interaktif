@props([
    'schema' => [], 
    'rows' => [],
    'modelText' => null,
    'modelJson' => null,
    'modelField' => null,
    'modelFile' => null,
    'modelPrefix' => null
])

@php
    $inputType = $schema['input_type'] ?? 'short_text';
    
    $bindText = $modelText ?? ($modelPrefix ? "{$modelPrefix}.answer" : 'answer_text');
    $bindFile = $modelFile ?? ($modelPrefix ? "{$modelPrefix}.file" : 'file');
    $bindJson = $modelJson ?? ($modelPrefix ? "{$modelPrefix}.rows" : 'answer_json');
    $bindField = $modelField ?? ($modelPrefix ? $modelPrefix : 'field_data');
@endphp

<div class="activity-renderer">
    @if($inputType === 'short_text')
        <flux:input wire:model="{{ $bindText }}" placeholder="Tulis jawaban singkat..." />
        
    @elseif($inputType === 'essay')
        <flux:textarea wire:model="{{ $bindText }}" placeholder="Tulis jawaban essay/uraian Anda di sini..." rows="6" />
        
    @elseif($inputType === 'file')
        <flux:input type="file" wire:model="{{ $bindFile }}" />
        <p class="mt-2 text-xs text-elkm-muted">Format yang didukung: PDF, JPG, PNG, DOCX (Maks 5MB)</p>
        
    @elseif($inputType === 'discussion')
        <flux:textarea wire:model="{{ $bindText }}" placeholder="Tuliskan pendapat, hasil diskusi, atau pertanyaan Anda di sini..." rows="5" />
        <p class="mt-2 text-xs text-elkm-muted">Jawaban Anda akan dibagikan ke Forum Diskusi agar dapat dilihat dan ditanggapi oleh guru serta teman kelas Anda.</p>
        
    @elseif($inputType === 'table')
        @php
            $columns = $schema['columns'] ?? [];
            $rowCount = count($rows) > 0 ? count($rows) : ($schema['min_rows'] ?? 5);
        @endphp
        
        <div class="overflow-x-auto border border-elkm-line rounded-xl">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-elkm-surface-2">
                        <th class="p-3 text-xs font-bold tracking-wider uppercase border-b border-elkm-line text-elkm-muted">No</th>
                        @foreach($columns as $col)
                            <th class="p-3 text-xs font-bold tracking-wider uppercase border-b border-elkm-line text-elkm-muted">{{ $col['label'] ?? $col['name'] ?? 'Column' }}</th>
                        @endforeach
                        @if($schema['allow_delete'] ?? true)
                            <th class="p-3 text-xs font-bold tracking-wider text-right uppercase border-b border-elkm-line text-elkm-muted">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $rowCount; $i++)
                        <tr class="bg-white border-b border-elkm-line last:border-b-0">
                            <td class="p-3 align-top">{{ $i + 1 }}</td>
                            @foreach($columns as $col)
                                <td class="p-3 align-top">
                                    @if(($col['type'] ?? 'text') === 'select')
                                        <flux:select wire:model="{{ $bindJson }}.{{ $i }}.{{ $col['name'] }}" placeholder="Pilih...">
                                            @foreach($col['options'] ?? ['Opsi 1', 'Opsi 2'] as $opt)
                                                <flux:select.option value="{{ $opt }}">{{ $opt }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    @elseif(($col['type'] ?? 'text') === 'readonly_text' || ($col['type'] ?? 'text') === 'computed')
                                        <div class="p-2 text-sm bg-gray-50 text-elkm-muted rounded-lg border border-transparent">
                                            {{ $rows[$i][$col['name']] ?? '-' }}
                                        </div>
                                    @else
                                        <flux:input wire:model="{{ $bindJson }}.{{ $i }}.{{ $col['name'] }}" placeholder="Ketik..." />
                                    @endif
                                </td>
                            @endforeach
                            @if($schema['allow_delete'] ?? true)
                                <td class="p-3 text-right align-top">
                                    <button type="button" wire:click="removeTableRow({{ $i }})" class="text-xs text-elkm-danger hover:underline">Hapus</button>
                                </td>
                            @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        
    @elseif($inputType === 'project_form' || $inputType === 'fields')
        @php
            $fields = $schema['fields'] ?? [];
        @endphp
        <div class="space-y-4">
            @foreach($fields as $field)
                @if(($field['type'] ?? 'text') === 'select')
                    <flux:select wire:model="{{ $bindField }}.{{ $field['name'] }}" label="{{ $field['label'] ?? $field['name'] }}">
                        <flux:select.option value="">Pilih...</flux:select.option>
                        @foreach($field['options'] ?? [] as $opt)
                            <flux:select.option value="{{ $opt }}">{{ $opt }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @elseif(($field['type'] ?? 'text') === 'textarea')
                    <flux:textarea wire:model="{{ $bindField }}.{{ $field['name'] }}" label="{{ $field['label'] ?? $field['name'] }}" rows="4" />
                @else
                    <flux:input wire:model="{{ $bindField }}.{{ $field['name'] }}" label="{{ $field['label'] ?? $field['name'] }}" />
                @endif
            @endforeach
        </div>
        
    @else
        <div class="p-4 border border-elkm-danger rounded-xl bg-[#fff1f1] text-[#b83333]">
            Tipe input schema tidak didukung: <strong>{{ $inputType }}</strong>
        </div>
    @endif
</div>
