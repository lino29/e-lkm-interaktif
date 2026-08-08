<div class="space-y-6">
    <x-elkm.page-header
        title="Penilaian KB"
        subtitle="Periksa seluruh jawaban siswa per kegiatan belajar, lalu berikan nilai maksimal 20 untuk setiap KB."
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Asesmen</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Penilaian KB</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <flux:card>
            <div class="text-sm font-semibold text-elkm-muted">Bobot per KB</div>
            <div class="mt-2 text-3xl font-black text-elkm-primary">20</div>
        </flux:card>
        <flux:card>
            <div class="text-sm font-semibold text-elkm-muted">Jumlah KB utama</div>
            <div class="mt-2 text-3xl font-black text-elkm-primary">5</div>
        </flux:card>
        <flux:card>
            <div class="text-sm font-semibold text-elkm-muted">Nilai maksimal</div>
            <div class="mt-2 text-3xl font-black text-elkm-primary">100</div>
        </flux:card>
    </div>

    <flux:card class="grid gap-4 md:grid-cols-3">
        <flux:field>
            <flux:label>Cari siswa</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Nama atau email siswa" />
        </flux:field>
        <flux:field>
            <flux:label>Modul</flux:label>
            <flux:select wire:model.live="moduleId">
                <flux:select.option value="">Semua modul</flux:select.option>
                @foreach ($modules as $module)
                    <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
        <flux:field>
            <flux:label>Status</flux:label>
            <flux:select wire:model.live="status">
                <flux:select.option value="pending">Menunggu penilaian</flux:select.option>
                <flux:select.option value="reviewed">Sudah dinilai</flux:select.option>
                <flux:select.option value="all">Semua status</flux:select.option>
            </flux:select>
        </flux:field>
    </flux:card>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.4fr)]">
        <div class="space-y-4">
            @forelse ($grades as $grade)
                <button
                    type="button"
                    wire:key="kb-grade-{{ $grade->id }}"
                    wire:click="selectSubmission({{ $grade->id }})"
                    @class([
                        'w-full rounded-3xl border bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-elkm-primary hover:shadow-md',
                        'border-elkm-primary ring-2 ring-elkm-primary/15' => $selectedGradeId === $grade->id,
                        'border-elkm-line' => $selectedGradeId !== $grade->id,
                    ])
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="truncate text-base font-bold text-elkm-text">{{ $grade->student->name }}</div>
                            <div class="mt-1 text-sm font-semibold text-elkm-primary">KB {{ $grade->learningUnit->order }} · {{ $grade->learningUnit->title }}</div>
                            <div class="mt-1 truncate text-xs text-elkm-muted">{{ $grade->learningUnit->module->title }}</div>
                        </div>
                        @if ($grade->score !== null)
                            <div class="shrink-0 rounded-2xl bg-emerald-50 px-3 py-2 text-center text-emerald-700">
                                <div class="text-lg font-black">{{ (float) $grade->score }}</div>
                                <div class="text-[10px] font-bold uppercase">dari 20</div>
                            </div>
                        @else
                            <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Perlu dinilai</span>
                        @endif
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-elkm-line pt-3 text-xs text-elkm-muted">
                        <span>Masuk {{ $grade->created_at->diffForHumans() }}</span>
                        <span class="font-bold text-elkm-primary">Buka jawaban →</span>
                    </div>
                </button>
            @empty
                <x-elkm.empty-state title="Belum ada jawaban untuk dinilai" description="Jawaban akan masuk setelah siswa menuntaskan kegiatan belajar." />
            @endforelse

            {{ $grades->links() }}
        </div>

        <div>
            @if ($selectedGrade)
                <section class="space-y-6 rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col justify-between gap-4 border-b border-elkm-line pb-5 sm:flex-row sm:items-start">
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider text-elkm-primary">Detail jawaban siswa</div>
                            <h2 class="mt-2 text-2xl font-black text-elkm-text">{{ $selectedGrade->student->name }}</h2>
                            <p class="mt-1 text-sm text-elkm-muted">KB {{ $selectedGrade->learningUnit->order }} · {{ $selectedGrade->learningUnit->title }}</p>
                        </div>
                        <button type="button" wire:click="closeSubmission" class="self-start rounded-xl border border-elkm-line px-3 py-2 text-sm font-bold text-elkm-muted transition hover:border-elkm-primary hover:text-elkm-primary">Tutup</button>
                    </div>

                    <div class="rounded-2xl border border-elkm-line bg-elkm-surface/60 p-4">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <div class="text-sm font-bold text-elkm-text">Rekap nilai KB 1–5</div>
                                <div class="mt-1 text-xs text-elkm-muted">Total nilai yang sudah diberikan guru.</div>
                            </div>
                            <div class="text-right">
                                <span class="text-3xl font-black text-elkm-primary">{{ (float) $studentModuleGrades->sum(fn ($grade) => (float) ($grade->score ?? 0)) }}</span>
                                <span class="text-sm font-bold text-elkm-muted">/ 100</span>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-5 gap-2">
                            @for ($kbOrder = 1; $kbOrder <= 5; $kbOrder++)
                                @php
                                    $kbGrade = $studentModuleGrades->first(fn ($grade) => $grade->learningUnit->order === $kbOrder);
                                @endphp
                                <div class="rounded-xl border border-elkm-line bg-white px-2 py-3 text-center">
                                    <div class="text-[10px] font-black uppercase text-elkm-muted">KB {{ $kbOrder }}</div>
                                    <div class="mt-1 text-lg font-black text-elkm-text">{{ $kbGrade?->score !== null ? (float) $kbGrade->score : '–' }}</div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($selectedAnswers as $answer)
                            <article wire:key="answer-detail-{{ $answer->id }}" class="rounded-2xl border border-elkm-line p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-elkm-text">{{ $answer->activity->title }}</h3>
                                        <p class="mt-1 text-xs font-semibold text-elkm-primary">{{ \Illuminate\Support\Str::headline($answer->activity->phase) }}</p>
                                    </div>
                                    <span class="text-xs text-elkm-muted">{{ $answer->submitted_at?->format('d M Y H:i') }}</span>
                                </div>

                                @if ($answer->activity->prompt)
                                    <div class="mt-3 rounded-xl bg-elkm-surface/60 p-3 text-sm text-elkm-muted">{{ $answer->activity->prompt }}</div>
                                @endif

                                <div class="mt-3">
                                    @if ($answer->activity->input_type === 'table' && $answer->answer_json)
                                        @php
                                            $columns = data_get($answer->activity->answer_schema, 'columns', []);
                                        @endphp
                                        <div class="overflow-x-auto rounded-xl border border-elkm-line">
                                            <table class="w-full min-w-[32rem] text-left text-sm">
                                                <thead class="bg-elkm-surface">
                                                    <tr>
                                                        @foreach ($columns as $column)
                                                            <th class="px-3 py-2 font-bold text-elkm-text">{{ $column['label'] ?? $column['name'] }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($answer->answer_json as $rowIndex => $row)
                                                        <tr wire:key="answer-row-{{ $answer->id }}-{{ $rowIndex }}" class="border-t border-elkm-line">
                                                            @foreach ($columns as $column)
                                                                <td class="px-3 py-2 text-elkm-muted">{{ $row[$column['name']] ?? '–' }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @elseif ($answer->answer_json)
                                        <div class="grid gap-2 rounded-xl border border-elkm-line bg-white p-3 text-sm">
                                            @foreach ((array) data_get($answer->answer_json, '0', $answer->answer_json) as $field => $value)
                                                <div class="grid gap-1 sm:grid-cols-[10rem_1fr]" wire:key="answer-field-{{ $answer->id }}-{{ $field }}">
                                                    <span class="font-bold text-elkm-text">{{ \Illuminate\Support\Str::headline($field) }}</span>
                                                    <span class="text-elkm-muted">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="whitespace-pre-wrap rounded-xl border border-elkm-line bg-white p-3 text-sm text-elkm-text">{{ $answer->answer_text ?: 'Tidak ada jawaban teks.' }}</div>
                                    @endif

                                    @if ($answer->file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($answer->file_path) }}" target="_blank" class="mt-3 inline-flex text-sm font-bold text-elkm-primary hover:underline">Lihat lampiran ↗</a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <x-elkm.empty-state title="Jawaban tidak ditemukan" description="Belum ada jawaban terkirim untuk KB ini." />
                        @endforelse
                    </div>

                    <form wire:submit="saveGrade" class="space-y-4 rounded-2xl border border-elkm-primary/25 bg-elkm-primary/5 p-4">
                        <div class="grid gap-4 md:grid-cols-[10rem_1fr]">
                            <flux:field>
                                <flux:label>Nilai KB</flux:label>
                                <flux:input wire:model="gradeScore" type="number" min="0" max="20" step="0.01" placeholder="0–20" />
                                <flux:error name="gradeScore" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Umpan balik guru</flux:label>
                                <flux:textarea wire:model="gradeFeedback" rows="3" placeholder="Tuliskan kekuatan jawaban dan bagian yang perlu diperbaiki." />
                                <flux:error name="gradeFeedback" />
                            </flux:field>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-elkm-muted">Nilai tidak dapat melebihi bobot maksimal 20.</p>
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">Simpan Penilaian</flux:button>
                        </div>
                    </form>
                </section>
            @else
                <div class="sticky top-6">
                    <x-elkm.empty-state title="Pilih jawaban siswa" description="Klik salah satu siswa di daftar untuk melihat seluruh jawaban dan memberikan nilai KB." />
                </div>
            @endif
        </div>
    </div>
</div>
