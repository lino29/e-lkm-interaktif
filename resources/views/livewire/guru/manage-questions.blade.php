<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('guru.assessments') }}">Asesmen</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Soal</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">Kelola Soal</flux:heading>
            <flux:text>Kelola soal pilihan ganda, benar/salah, menjodohkan, isian singkat, dan uraian.</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button type="button" :href="route('guru.assessments')" wire:navigate>Kembali ke Asesmen</flux:button>
            <flux:button type="button" variant="ghost" :href="route('guru.rubrics')" wire:navigate>Rubrik Uraian</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-6 xl:grid-cols-[560px_1fr]">
        <flux:card class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editingQuestionId ? 'Edit Soal' : 'Tambah Soal' }}</flux:heading>
                    <flux:text>Form akan menyesuaikan berdasarkan tipe soal yang Anda pilih.</flux:text>
                </div>
                @if ($editingQuestionId)
                    <flux:button type="button" size="sm" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                @endif
            </div>

            <form wire:submit="save" class="space-y-5">
                <flux:field>
                    <flux:label>Asesmen</flux:label>
                    <flux:select wire:model="assessment_id">
                        <flux:select.option value="">Pilih asesmen</flux:select.option>
                        @foreach ($assessments as $assessment)
                            <flux:select.option value="{{ $assessment->id }}">{{ $assessment->title }} - {{ $assessment->module->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="assessment_id" />
                </flux:field>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Tipe soal</flux:label>
                        <flux:select wire:model.live="question_type">
                            @foreach (['multiple_choice', 'complex_multiple_choice', 'true_false', 'matching', 'short_answer', 'essay'] as $type)
                                <flux:select.option value="{{ $type }}">{{ \Illuminate\Support\Str::headline($type) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="question_type" />
                    </flux:field>

                    <div class="grid gap-4 grid-cols-2">
                        <flux:field>
                            <flux:label>Bobot</flux:label>
                            <flux:input type="number" step="0.01" min="0.01" wire:model="weight" />
                            <flux:error name="weight" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Urutan</flux:label>
                            <flux:input type="number" min="1" wire:model="order" />
                            <flux:error name="order" />
                        </flux:field>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Pertanyaan</flux:label>
                    <flux:textarea rows="4" wire:model="question_text" />
                    <flux:error name="question_text" />
                </flux:field>

                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-800 pt-4">
                    <flux:heading size="md">Opsi & Kunci Jawaban</flux:heading>

                    @if (in_array($question_type, ['multiple_choice', 'complex_multiple_choice']))
                        <div class="space-y-3">
                            @foreach($options as $key => $option)
                                <div class="flex items-start gap-3" wire:key="option-{{ $key }}">
                                    <div class="pt-2">
                                        @if($question_type === 'multiple_choice')
                                            <flux:radio wire:click="toggleCorrectAnswer('{{ $key }}')" :checked="in_array($key, $correct_answers)" />
                                        @else
                                            <flux:checkbox wire:click="toggleCorrectAnswer('{{ $key }}')" :checked="in_array($key, $correct_answers)" />
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <flux:input wire:model="options.{{ $key }}" placeholder="Opsi {{ $key }}" />
                                    </div>
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeOption('{{ $key }}')" class="mt-1" color="red" />
                                </div>
                            @endforeach
                            <flux:button size="sm" wire:click="addOption" icon="plus">Tambah Opsi</flux:button>
                            @error('correct_answers') <div class="text-sm text-red-500">{{ $message }}</div> @enderror
                        </div>

                    @elseif ($question_type === 'true_false')
                        <div class="flex items-center gap-6">
                            <flux:radio.group wire:model="correct_answers.0" label="Kunci Jawaban">
                                <flux:radio value="True" label="Benar" />
                                <flux:radio value="False" label="Salah" />
                            </flux:radio.group>
                        </div>

                    @elseif ($question_type === 'matching')
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-3">
                                <div class="font-medium text-sm">Baris Kiri</div>
                                @foreach($matching_left as $key => $val)
                                    <div class="flex gap-2" wire:key="match-left-{{ $key }}">
                                        <div class="flex-1">
                                            <flux:input wire:model="matching_left.{{ $key }}" placeholder="Baris {{ $key }}" />
                                        </div>
                                        <flux:button variant="ghost" icon="trash" wire:click="removeMatchingLeft('{{ $key }}')" color="red" />
                                    </div>
                                @endforeach
                                <flux:button size="sm" wire:click="addMatchingLeft" icon="plus">Tambah Baris Kiri</flux:button>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-3">
                                <div class="font-medium text-sm">Baris Kanan (Opsi Jawaban & Pengecoh)</div>
                                @foreach($matching_right as $key => $val)
                                    <div class="flex gap-2" wire:key="match-right-{{ $key }}">
                                        <div class="flex-1">
                                            <flux:input wire:model="matching_right.{{ $key }}" placeholder="Opsi {{ $key }}" />
                                        </div>
                                        <flux:button variant="ghost" icon="trash" wire:click="removeMatchingRight('{{ $key }}')" color="red" />
                                    </div>
                                @endforeach
                                <flux:button size="sm" wire:click="addMatchingRight" icon="plus">Tambah Baris Kanan</flux:button>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800 pt-4">
                            <div class="font-medium text-sm mb-3">Kunci Jawaban Pasangan</div>
                            <div class="grid gap-3">
                                @foreach($matching_left as $lKey => $lVal)
                                    <div class="flex items-center gap-3">
                                        <div class="w-1/3 truncate text-sm font-medium">{{ $lKey }}. {{ $lVal ?: '...' }}</div>
                                        <div><flux:icon.arrow-right class="size-4 text-zinc-400" /></div>
                                        <div class="w-2/3">
                                            <flux:select wire:model="matching_answers.{{ $lKey }}">
                                                <flux:select.option value="">-- Pilih --</flux:select.option>
                                                @foreach($matching_right as $rKey => $rVal)
                                                    <flux:select.option value="{{ $rKey }}">{{ $rKey }}. {{ $rVal }}</flux:select.option>
                                                @endforeach
                                            </flux:select>
                                        </div>
                                    </div>
                                @endforeach
                                @error('matching_answers') <div class="text-sm text-red-500">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    @elseif (in_array($question_type, ['short_answer', 'essay'], true))
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Jawaban Acuan</flux:label>
                                <flux:textarea wire:model="reference_answer" rows="3" placeholder="Tuliskan jawaban yang diharapkan di sini..." />
                            </flux:field>
                            
                            <flux:field>
                                <flux:label>Kata Kunci (Keywords)</flux:label>
                                <flux:input wire:model="keywords" placeholder="Contoh: matahari, panel surya, terbarukan" />
                                <flux:text class="text-xs mt-1">Pisahkan dengan koma. Digunakan untuk Keyword-based scoring.</flux:text>
                            </flux:field>

                            @if ($question_type === 'essay')
                                <flux:switch wire:model="use_ai_scoring" label="Gunakan bantuan AI/kemiripan" description="Sistem akan memakai bantuan scoring uraian jika konfigurasi tersedia." />
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <flux:button type="submit" variant="primary">
                        {{ $editingQuestionId ? 'Simpan Perubahan' : 'Simpan Soal' }}
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <flux:heading size="lg">Bank Soal</flux:heading>
                    <flux:text>Tampilkan soal berdasarkan asesmen.</flux:text>
                </div>
                <div class="w-full sm:w-72 shrink-0">
                    <flux:select wire:model.live="filter_assessment_id" placeholder="Pilih Asesmen">
                        <flux:select.option value="">Semua Asesmen (Sembunyikan)</flux:select.option>
                        @foreach ($assessments as $assessment)
                            <flux:select.option value="{{ $assessment->id }}">{{ \Illuminate\Support\Str::limit($assessment->title, 40) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            @if (! $filter_assessment_id)
                <flux:card>
                    <div class="flex flex-col items-center justify-center py-10 text-center text-zinc-500">
                        <flux:icon.document-text class="size-12 mb-3 opacity-50" />
                        <flux:heading size="md" class="text-zinc-700 dark:text-zinc-300">Pilih Asesmen Terlebih Dahulu</flux:heading>
                        <flux:text class="max-w-xs mt-1">Silakan pilih asesmen pada menu dropdown di atas untuk melihat dan mengelola daftar soal yang ada di dalamnya.</flux:text>
                    </div>
                </flux:card>
            @else
                <div class="mb-1">
                    <flux:text>{{ $questions->count() }} soal tersedia pada asesmen yang dipilih.</flux:text>
                </div>

                @forelse ($questions->groupBy('assessment_id') as $assessmentQuestions)
                    @php($assessment = $assessmentQuestions->first()->assessment)
                    <flux:card wire:key="assessment-question-group-{{ $assessment->id }}" class="space-y-3">
                        <div>
                            <div class="font-semibold">{{ $assessment->title }}</div>
                            <flux:text>{{ $assessment->module->title }} - {{ $assessmentQuestions->count() }} soal</flux:text>
                        </div>

                        <div class="space-y-2">
                            @foreach ($assessmentQuestions as $question)
                                <div wire:key="question-{{ $question->id }}" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-medium">{{ $question->order }}. {{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</span>
                                                <flux:badge size="sm">{{ \Illuminate\Support\Str::headline($question->question_type) }}</flux:badge>
                                                <flux:badge size="sm" color="blue">Bobot {{ $question->weight }}</flux:badge>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <flux:button type="button" size="sm" wire:click="edit({{ $question->id }})">Edit</flux:button>
                                            <flux:button type="button" size="sm" variant="danger" wire:click="delete({{ $question->id }})" wire:confirm="Hapus soal ini?">Hapus</flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @empty
                    <flux:card>
                        <flux:text>Belum ada soal pada asesmen ini. Silakan tambahkan soal pertama di sebelah kiri.</flux:text>
                    </flux:card>
                @endforelse
            @endif
        </section>
    </div>
</div>
