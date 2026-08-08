<div class="space-y-6">
    <x-elkm.page-header
        title="Nilai Saya"
        subtitle="Pantau hasil asesmen sumatif dan nilai Kegiatan Belajar pada semester berjalan."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Nilai Saya</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <div class="flex flex-col justify-between gap-3 rounded-3xl border border-elkm-line bg-white p-4 shadow-sm sm:flex-row sm:items-center">
        <div>
            <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Periode aktif</div>
            <div class="mt-1 font-bold text-elkm-text">{{ $semesterLabel }}</div>
            <div class="mt-1 text-xs text-elkm-muted">{{ $semesterStart->format('d M Y') }} – {{ $semesterEnd->format('d M Y') }}</div>
        </div>
        <div class="inline-flex rounded-2xl bg-elkm-surface p-1" role="tablist" aria-label="Jenis nilai">
            <button
                type="button"
                wire:click="showTab('summative')"
                @class([
                    'rounded-xl px-4 py-2.5 text-sm font-bold transition',
                    'bg-white text-elkm-primary shadow-sm' => $activeTab === 'summative',
                    'text-elkm-muted hover:text-elkm-text' => $activeTab !== 'summative',
                ])
            >Nilai Asesmen Sumatif</button>
            <button
                type="button"
                wire:click="showTab('kb')"
                @class([
                    'rounded-xl px-4 py-2.5 text-sm font-bold transition',
                    'bg-white text-elkm-primary shadow-sm' => $activeTab === 'kb',
                    'text-elkm-muted hover:text-elkm-text' => $activeTab !== 'kb',
                ])
            >Nilai KB</button>
        </div>
    </div>

    @if ($activeTab === 'summative')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.3fr)]">
            <div class="space-y-4">
                @forelse ($attempts as $attempt)
                    @php
                        $percentage = (float) $attempt->max_score > 0
                            ? round(((float) $attempt->total_score / (float) $attempt->max_score) * 100)
                            : 0;
                    @endphp
                    <button
                        type="button"
                        wire:key="summative-attempt-{{ $attempt->id }}"
                        wire:click="showAssessmentDetail({{ $attempt->id }})"
                        @class([
                            'w-full rounded-3xl border bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-elkm-primary hover:shadow-md',
                            'border-elkm-primary ring-2 ring-elkm-primary/15' => $selectedAssessmentAttemptId === $attempt->id,
                            'border-elkm-line' => $selectedAssessmentAttemptId !== $attempt->id,
                        ])
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="truncate text-base font-bold text-elkm-text">{{ $attempt->assessment->title }}</div>
                                <div class="mt-1 truncate text-sm text-elkm-muted">{{ $attempt->assessment->module->title }}</div>
                            </div>
                            <div class="shrink-0 rounded-2xl bg-elkm-primary/10 px-4 py-2 text-center text-elkm-primary">
                                <div class="text-2xl font-black">{{ $percentage }}</div>
                                <div class="text-[10px] font-black uppercase">Nilai</div>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-elkm-line pt-3 text-xs">
                            <span class="text-elkm-muted">Percobaan {{ $attempt->attempt_number }} · {{ $attempt->submitted_at?->format('d M Y H:i') }}</span>
                            <span @class([
                                'rounded-full px-2.5 py-1 font-bold',
                                'bg-emerald-50 text-emerald-700' => $attempt->status === 'tuntas',
                                'bg-amber-50 text-amber-700' => $attempt->status !== 'tuntas',
                            ])>{{ \Illuminate\Support\Str::headline($attempt->status) }}</span>
                        </div>
                    </button>
                @empty
                    <x-elkm.empty-state title="Belum ada nilai sumatif" description="Hasil asesmen akhir yang sudah disubmit pada semester ini akan tampil di sini." />
                @endforelse
            </div>

            <div>
                @if ($selectedAttempt)
                    @php
                        $answersByQuestion = $selectedAttempt->studentAnswers->keyBy('question_id');
                    @endphp
                    <section class="space-y-5 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
                        <div class="flex items-start justify-between gap-4 border-b border-elkm-line pb-4">
                            <div>
                                <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Detail asesmen sumatif</div>
                                <h2 class="mt-2 text-xl font-black text-elkm-text">{{ $selectedAttempt->assessment->title }}</h2>
                                <p class="mt-1 text-sm text-elkm-muted">Nilai {{ (float) $selectedAttempt->total_score }}/{{ (float) $selectedAttempt->max_score }}</p>
                            </div>
                            <button type="button" wire:click="closeDetail" class="rounded-xl border border-elkm-line px-3 py-2 text-sm font-bold text-elkm-muted hover:border-elkm-primary hover:text-elkm-primary">Tutup</button>
                        </div>

                        <div class="space-y-4">
                            @foreach ($selectedAttempt->assessment->questions as $question)
                                @php
                                    $studentAnswer = $answersByQuestion->get($question->id);
                                    $rawAnswer = $studentAnswer?->answer_json ?? $studentAnswer?->answer_text;
                                    $options = (array) ($question->options ?? []);
                                @endphp
                                <article wire:key="score-question-{{ $question->id }}" class="rounded-2xl border border-elkm-line p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <h3 class="font-bold leading-6 text-elkm-text">{{ $question->order }}. {{ $question->question_text }}</h3>
                                        <span class="shrink-0 rounded-full bg-elkm-surface px-2.5 py-1 text-xs font-black text-elkm-primary">{{ (float) ($studentAnswer?->score ?? 0) }} poin</span>
                                    </div>
                                    <div class="mt-3 rounded-xl bg-elkm-surface/70 p-3 text-sm text-elkm-text">
                                        <div class="mb-1 text-[10px] font-black uppercase tracking-wider text-elkm-muted">Jawaban Anda</div>
                                        @if (is_array($rawAnswer))
                                            @foreach ($rawAnswer as $answerKey => $answerValue)
                                                <div>{{ is_string($answerKey) ? $answerKey.': ' : '' }}{{ is_scalar($answerValue) ? $answerValue : json_encode($answerValue) }}</div>
                                            @endforeach
                                        @elseif (isset($options[(string) $rawAnswer]))
                                            {{ $rawAnswer }}. {{ is_scalar($options[(string) $rawAnswer]) ? $options[(string) $rawAnswer] : $rawAnswer }}
                                        @else
                                            {{ filled($rawAnswer) ? $rawAnswer : 'Tidak ada jawaban.' }}
                                        @endif
                                    </div>
                                    @if ($studentAnswer?->feedback)
                                        <div class="mt-3 text-sm text-elkm-muted"><span class="font-bold text-elkm-text">Umpan balik:</span> {{ $studentAnswer->feedback }}</div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @else
                    <x-elkm.empty-state title="Pilih hasil asesmen" description="Klik salah satu asesmen untuk melihat kembali jawaban yang Anda masukkan." />
                @endif
            </div>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($learningUnitGrades->groupBy(fn ($grade) => $grade->learningUnit->module_id) as $moduleGrades)
                @php
                    $firstGrade = $moduleGrades->first();
                @endphp
                <section wire:key="kb-module-{{ $firstGrade->learningUnit->module_id }}" class="rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Rekap nilai KB</div>
                            <h2 class="mt-1 text-xl font-black text-elkm-text">{{ $firstGrade->learningUnit->module->title }}</h2>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-4xl font-black text-elkm-primary">{{ (float) $moduleGrades->sum(fn ($grade) => (float) ($grade->score ?? 0)) }}</span>
                            <span class="font-bold text-elkm-muted">/ 100</span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @for ($kbOrder = 1; $kbOrder <= 5; $kbOrder++)
                            @php
                                $grade = $moduleGrades->first(fn ($item) => $item->learningUnit->order === $kbOrder);
                            @endphp
                            @if ($grade)
                                <button
                                    type="button"
                                    wire:key="student-kb-grade-{{ $grade->id }}"
                                    wire:click="showLearningUnitDetail({{ $grade->id }})"
                                    @class([
                                        'rounded-2xl border p-4 text-left transition hover:-translate-y-0.5 hover:border-elkm-primary',
                                        'border-elkm-primary bg-elkm-primary/5 ring-2 ring-elkm-primary/10' => $selectedLearningUnitGradeId === $grade->id,
                                        'border-elkm-line bg-elkm-surface/40' => $selectedLearningUnitGradeId !== $grade->id,
                                    ])
                                >
                                    <div class="text-xs font-black uppercase text-elkm-muted">KB {{ $kbOrder }}</div>
                                    <div class="mt-2 text-3xl font-black text-elkm-text">{{ $grade->score !== null ? (float) $grade->score : '–' }}</div>
                                    <div class="mt-1 text-xs font-semibold text-elkm-muted">dari 20</div>
                                </button>
                            @else
                                <div class="rounded-2xl border border-dashed border-elkm-line p-4 text-left opacity-70">
                                    <div class="text-xs font-black uppercase text-elkm-muted">KB {{ $kbOrder }}</div>
                                    <div class="mt-2 text-3xl font-black text-elkm-muted">–</div>
                                    <div class="mt-1 text-xs text-elkm-muted">Belum tersedia</div>
                                </div>
                            @endif
                        @endfor
                    </div>
                </section>
            @endforeach

            @if ($learningUnitGrades->isEmpty())
                <x-elkm.empty-state title="Belum ada nilai KB" description="Nilai akan tampil setelah Anda menuntaskan KB dan guru selesai memberikan penilaian." />
            @endif

            @if ($selectedLearningUnitGrade)
                <section class="space-y-5 rounded-3xl border border-elkm-primary/25 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex items-start justify-between gap-4 border-b border-elkm-line pb-4">
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Detail Nilai KB {{ $selectedLearningUnitGrade->learningUnit->order }}</div>
                            <h2 class="mt-2 text-xl font-black text-elkm-text">{{ $selectedLearningUnitGrade->learningUnit->title }}</h2>
                            <p class="mt-1 text-sm text-elkm-muted">Nilai {{ $selectedLearningUnitGrade->score !== null ? (float) $selectedLearningUnitGrade->score : 'belum diberikan' }}/20</p>
                        </div>
                        <button type="button" wire:click="closeDetail" class="rounded-xl border border-elkm-line px-3 py-2 text-sm font-bold text-elkm-muted hover:border-elkm-primary hover:text-elkm-primary">Tutup</button>
                    </div>

                    <div class="rounded-2xl bg-elkm-primary/5 p-4">
                        <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Umpan balik guru</div>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-elkm-text">{{ $selectedLearningUnitGrade->feedback ?: 'Guru belum menambahkan umpan balik tertulis.' }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($selectedLearningUnitAnswers as $answer)
                            <article wire:key="student-kb-answer-{{ $answer->id }}" class="rounded-2xl border border-elkm-line p-4">
                                <h3 class="font-bold text-elkm-text">{{ $answer->activity->title }}</h3>
                                <div class="mt-1 text-xs font-semibold text-elkm-primary">{{ \Illuminate\Support\Str::headline($answer->activity->phase) }}</div>
                                <div class="mt-3 whitespace-pre-wrap rounded-xl bg-elkm-surface/70 p-3 text-sm text-elkm-text">
                                    @if ($answer->answer_json)
                                        @foreach ((array) data_get($answer->answer_json, '0', $answer->answer_json) as $field => $value)
                                            <div><span class="font-bold">{{ \Illuminate\Support\Str::headline($field) }}:</span> {{ is_scalar($value) ? $value : json_encode($value) }}</div>
                                        @endforeach
                                    @else
                                        {{ $answer->answer_text ?: 'Tidak ada jawaban teks.' }}
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @endif
</div>
