<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $activity->title }}</flux:heading>
        <flux:text>{{ $activity->learningUnit->title }} · {{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text>
    </div>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <flux:card>
        <p class="text-sm leading-6">{{ $activity->prompt }}</p>
    </flux:card>
    
    @if($answer && $answer->status === 'reviewed')
        <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-green-500">
            <div class="font-semibold text-green-600 mb-2">Jawaban telah dinilai (Nilai: {{ $answer->score ?? '-' }})</div>
            @if($answer->teacher_feedback)
                <div class="text-sm">
                    <strong>Feedback Guru:</strong><br/>
                    {{ $answer->teacher_feedback }}
                </div>
            @endif
        </flux:card>
    @endif
    
    <form class="space-y-4">
        @if (in_array($activity->input_type, ['short_text', 'essay', 'discussion']))
            <flux:field>
                <flux:label>Jawaban</flux:label>
                @if($activity->input_type === 'short_text')
                    <flux:input wire:model="answer_text" :disabled="$answer?->status === 'reviewed'" />
                @else
                    <flux:textarea wire:model="answer_text" rows="5" :disabled="$answer?->status === 'reviewed'" />
                @endif
                <flux:error name="answer_text" />
            </flux:field>
        @endif
        
        @if ($activity->input_type === 'table')
            @php
                $columns = data_get($activity->answer_schema, 'columns', []);
                $allowAdd = data_get($activity->answer_schema, 'allow_add', true);
            @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            @foreach($columns as $col)
                                <th scope="col" class="px-4 py-3">{{ $col['label'] ?? $col['name'] }}</th>
                            @endforeach
                            @if(!($answer?->status === 'reviewed'))
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($table_data as $index => $row)
                            <tr class="bg-white border-b dark:bg-zinc-900 dark:border-zinc-700">
                                @foreach($columns as $col)
                                    <td class="px-4 py-2">
                                        <flux:input 
                                            wire:model="table_data.{{ $index }}.{{ $col['name'] }}" 
                                            :disabled="$answer?->status === 'reviewed'" 
                                            type="{{ $col['type'] === 'number' ? 'number' : 'text' }}" 
                                        />
                                    </td>
                                @endforeach
                                @if(!($answer?->status === 'reviewed'))
                                    <td class="px-4 py-2 text-right">
                                        <flux:button size="sm" variant="danger" wire:click="removeTableRow({{ $index }})">Hapus</flux:button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($allowAdd && !($answer?->status === 'reviewed'))
                <flux:button size="sm" wire:click="addTableRow" class="mt-2">Tambah Baris</flux:button>
            @endif
            <flux:error name="table_data" />
        @endif
        
        @if ($activity->input_type === 'file')
            <flux:field>
                <flux:label>Upload file</flux:label>
                <flux:input type="file" wire:model="file" :disabled="$answer?->status === 'reviewed'" />
                <flux:error name="file" />
                @if($answer?->file_path)
                    <div class="mt-2 text-sm">
                        File tersimpan: <a href="{{ Storage::url($answer->file_path) }}" target="_blank" class="text-blue-500 underline">Lihat File</a>
                    </div>
                @endif
            </flux:field>
        @endif
        
        @if(!($answer?->status === 'reviewed'))
            <div class="flex gap-2">
                <flux:button type="button" wire:click="saveDraft">Simpan Draft</flux:button>
                <flux:button type="button" variant="primary" wire:click="submit" wire:confirm="Yakin ingin mengirim? Jawaban yang disubmit akan dikunci.">Kirim Jawaban</flux:button>
            </div>
        @endif
    </form>
</div>
