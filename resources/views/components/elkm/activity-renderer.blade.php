@props(['schema' => [], 'modelPrefix' => 'answers'])

@php
    $inputType = $schema['input_type'] ?? 'short_text';
@endphp

<div class="activity-renderer">
    @if($inputType === 'short_text')
        <flux:input wire:model="{{ $modelPrefix }}.answer" placeholder="Tulis jawaban singkat..." />
        
    @elseif($inputType === 'essay')
        <flux:textarea wire:model="{{ $modelPrefix }}.answer" placeholder="Tulis jawaban essay/uraian Anda di sini..." rows="6" />
        
    @elseif($inputType === 'file')
        <flux:input type="file" wire:model="{{ $modelPrefix }}.file" />
        <p class="text-xs text-elkm-muted mt-2">Format yang didukung: PDF, JPG, PNG, DOCX (Maks 5MB)</p>
        
    @elseif($inputType === 'discussion')
        <div class="discussion-placeholder p-4 border border-elkm-line rounded-xl bg-elkm-surface-2 text-center text-elkm-muted">
            <span class="text-2xl block mb-2">💬</span>
            <p>Ruang diskusi akan terbuka saat aktivitas dimulai.</p>
        </div>
        
    @elseif($inputType === 'table')
        @php
            $columns = $schema['columns'] ?? [];
            $minRows = $schema['min_rows'] ?? 5;
            // Since this is a generic component, we rely on Livewire to provide the rows array
            // Assuming the parent Livewire component has initialized `$answers['rows']` as an array
        @endphp
        
        <div class="overflow-x-auto border border-elkm-line rounded-xl">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-elkm-surface-2">
                        <th class="p-3 border-b border-elkm-line text-elkm-muted font-bold uppercase text-xs tracking-wider">No</th>
                        @foreach($columns as $col)
                            <th class="p-3 border-b border-elkm-line text-elkm-muted font-bold uppercase text-xs tracking-wider">{{ $col['name'] ?? 'Column' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Di Blade biasa kita tidak bisa for loop row kosong yang belum ada di state tanpa JS/Livewire entangle. --}}
                    {{-- Pendekatan di sini adalah melempar event ke komponen parent atau me-render berdasarkan data dari state parent --}}
                    
                    {{-- Mockup statis jika array kosong (misal saat pratinjau oleh guru) --}}
                    @for($i = 0; $i < $minRows; $i++)
                        <tr class="border-b border-elkm-line last:border-b-0 bg-white">
                            <td class="p-3 align-top">{{ $i + 1 }}</td>
                            @foreach($columns as $col)
                                <td class="p-3 align-top">
                                    @if(($col['type'] ?? 'text') === 'select')
                                        <flux:select wire:model="{{ $modelPrefix }}.rows.{{ $i }}.{{ $col['name'] }}" placeholder="Pilih...">
                                            @foreach($col['options'] ?? ['Opsi 1', 'Opsi 2'] as $opt)
                                                <flux:select.option value="{{ $opt }}">{{ $opt }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    @else
                                        <flux:input wire:model="{{ $modelPrefix }}.rows.{{ $i }}.{{ $col['name'] }}" placeholder="Ketik {{ strtolower($col['name']) }}..." />
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        
    @elseif($inputType === 'project_form')
        <div class="space-y-4">
            <flux:input wire:model="{{ $modelPrefix }}.project_title" label="Judul Proyek" placeholder="Tuliskan judul proyek kelompok Anda..." />
            <flux:textarea wire:model="{{ $modelPrefix }}.project_desc" label="Deskripsi Proyek" placeholder="Jelaskan secara singkat apa yang akan Anda buat..." rows="4" />
            <flux:input type="file" wire:model="{{ $modelPrefix }}.proposal_file" label="Upload Proposal (Opsional)" />
        </div>
        
    @else
        <div class="p-4 border border-elkm-danger rounded-xl bg-[#fff1f1] text-[#b83333]">
            Tipe input schema tidak didukung: <strong>{{ $inputType }}</strong>
        </div>
    @endif
</div>
