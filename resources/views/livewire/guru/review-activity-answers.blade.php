<div class="space-y-6">
    <div>
        <flux:heading size="xl">Review Jawaban Aktivitas</flux:heading>
        <flux:text>Berikan nilai dan umpan balik untuk aktivitas siswa (terutama essay/penalaran).</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <flux:field>
            <flux:label>Modul</flux:label>
            <flux:select wire:model.live="moduleId">
                <flux:select.option value="">Semua Modul</flux:select.option>
                @foreach($modules as $module)
                    <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Fase</flux:label>
            <flux:select wire:model.live="phase">
                <flux:select.option value="">Semua Fase</flux:select.option>
                @foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan'] as $phaseOption)
                    <flux:select.option value="{{ $phaseOption }}">{{ \Illuminate\Support\Str::headline($phaseOption) }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Status</flux:label>
            <flux:select wire:model.live="status">
                <flux:select.option value="">Semua Status</flux:select.option>
                <flux:select.option value="submitted">Menunggu Review</flux:select.option>
                <flux:select.option value="reviewed">Telah Direview</flux:select.option>
            </flux:select>
        </flux:field>
    </div>

    <div class="space-y-4">
        @forelse($answers as $answer)
            <flux:card wire:key="answer-{{ $answer->id }}">
                <div class="flex items-start justify-between border-b pb-3 mb-3 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <div class="font-semibold">{{ $answer->user->name }} - {{ $answer->activity->learningUnit->module->title }}</div>
                        <flux:text>{{ $answer->activity->title }} ({{ \Illuminate\Support\Str::headline($answer->activity->phase) }})</flux:text>
                        <flux:text class="text-xs mt-1">Disubmit: {{ $answer->submitted_at?->format('d M Y H:i') }}</flux:text>
                    </div>
                    <div>
                        @if($answer->status === 'reviewed')
                            <flux:badge color="green" size="sm">Direview</flux:badge>
                        @else
                            <flux:badge color="blue" size="sm">Menunggu Review</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <div class="font-semibold text-sm mb-1">Pertanyaan/Instruksi:</div>
                    <div class="text-sm p-3 bg-zinc-50 dark:bg-zinc-900 rounded-md">
                        {{ $answer->activity->prompt }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="font-semibold text-sm mb-1">Jawaban Siswa:</div>
                    
                    @if($answer->activity->input_type === 'table' && $answer->answer_json)
                        @php
                            $columns = data_get($answer->activity->answer_schema, 'columns', []);
                        @endphp
                        <div class="overflow-x-auto rounded-md border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-zinc-100 dark:bg-zinc-800">
                                    <tr>
                                        @foreach($columns as $col)
                                            <th class="px-3 py-2 font-semibold">{{ $col['label'] ?? $col['name'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($answer->answer_json as $row)
                                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                            @foreach($columns as $col)
                                                <td class="px-3 py-2">{{ $row[$col['name']] ?? '' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($answer->answer_text)
                        <div class="text-sm p-3 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-md whitespace-pre-wrap">{{ $answer->answer_text }}</div>
                    @else
                        <flux:text>Tidak ada jawaban teks.</flux:text>
                    @endif

                    @if($answer->file_path)
                        <div class="mt-2 text-sm">
                            <a href="{{ Storage::url($answer->file_path) }}" target="_blank" class="text-blue-500 underline">Lihat Lampiran File</a>
                        </div>
                    @endif
                </div>

                <form wire:submit="saveReview({{ $answer->id }}, $event.target.score.value, $event.target.feedback.value)" class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-md space-y-3">
                    <div class="grid gap-3 md:grid-cols-4">
                        <div class="md:col-span-1">
                            <flux:field>
                                <flux:label>Nilai (0-100)</flux:label>
                                <flux:input type="number" name="score" min="0" max="100" value="{{ $answer->score }}" />
                            </flux:field>
                        </div>
                        <div class="md:col-span-3">
                            <flux:field>
                                <flux:label>Feedback Guru</flux:label>
                                <flux:textarea name="feedback" rows="2">{{ $answer->teacher_feedback }}</flux:textarea>
                            </flux:field>
                        </div>
                    </div>
                    <flux:button type="submit" size="sm" variant="primary">Simpan Review</flux:button>
                </form>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>Belum ada jawaban aktivitas yang sesuai kriteria pencarian.</flux:text>
            </flux:card>
        @endforelse

        <div>
            {{ $answers->links() }}
        </div>
    </div>
</div>
