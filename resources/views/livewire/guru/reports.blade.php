<div class="space-y-8">
    <header class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
        <div class="max-w-3xl">
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Laporan</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" level="1">Laporan Pembelajaran</flux:heading>
            <p class="mt-2 text-sm leading-6 text-elkm-muted md:text-base">
                Pantau hasil asesmen, progres belajar, proyek, remedial, dan partisipasi murid dalam satu halaman.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row" aria-label="Aksi ekspor laporan">
            <flux:button
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                icon="document-text"
                variant="primary"
                :disabled="$module_id === null"
            >
                Export Excel
            </flux:button>
            <flux:button
                wire:click="exportPdf"
                wire:loading.attr="disabled"
                wire:target="exportPdf"
                icon="document-arrow-down"
                :disabled="$module_id === null"
            >
                Export PDF
            </flux:button>
        </div>
    </header>

    <nav aria-label="Navigasi bagian laporan" class="overflow-x-auto rounded-2xl border border-elkm-line bg-white p-2 shadow-sm">
        <div class="flex min-w-max gap-1">
            <a href="#ringkasan" class="rounded-xl px-3 py-2 text-sm font-semibold text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary">Ringkasan</a>
            <a href="#attempt-asesmen" class="rounded-xl px-3 py-2 text-sm font-semibold text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary">Attempt Asesmen</a>
            <a href="#progres-remedial" class="rounded-xl px-3 py-2 text-sm font-semibold text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary">Progres & Remedial</a>
            <a href="#proyek" class="rounded-xl px-3 py-2 text-sm font-semibold text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary">Proyek</a>
            <a href="#diskusi" class="rounded-xl px-3 py-2 text-sm font-semibold text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary">Diskusi</a>
        </div>
    </nav>

    <section aria-labelledby="filter-laporan-heading" class="rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
            <div>
                <h2 id="filter-laporan-heading" class="text-lg font-bold text-elkm-text">Filter laporan</h2>
                <p class="mt-1 text-sm text-elkm-muted">Gunakan filter untuk mempersempit data. Ringkasan mengikuti modul yang dipilih.</p>
            </div>
            <div class="text-sm font-semibold text-elkm-primary" aria-live="polite">
                {{ $activeFilterCount }} filter aktif
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4" role="search">
            <flux:field class="md:col-span-2">
                <flux:label>Cari data laporan</flux:label>
                <flux:input
                    type="search"
                    icon="magnifying-glass"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Nama murid, asesmen, proyek, atau diskusi"
                    aria-describedby="report-search-help"
                />
                <flux:description id="report-search-help">Pencarian diterapkan pada seluruh bagian laporan yang relevan.</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Modul</flux:label>
                <flux:select wire:model.live="module_id">
                    <flux:select.option value="">Semua modul</flux:select.option>
                    @foreach ($modules as $module)
                        <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="module_id" />
            </flux:field>

            <flux:field>
                <flux:label>Status asesmen</flux:label>
                <flux:select wire:model.live="attempt_status">
                    <flux:select.option value="">Semua status</flux:select.option>
                    <flux:select.option value="tuntas">Tuntas</flux:select.option>
                    <flux:select.option value="remedial">Remedial</flux:select.option>
                    <flux:select.option value="sedang_dikerjakan">Sedang dikerjakan</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Status proyek</flux:label>
                <flux:select wire:model.live="project_status">
                    <flux:select.option value="">Semua status</flux:select.option>
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="submitted">Dikirim</flux:select.option>
                    <flux:select.option value="reviewed">Sudah direview</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex items-end md:col-span-2 xl:col-span-3">
                <flux:button
                    type="button"
                    wire:click="resetFilters"
                    icon="arrow-path"
                    variant="ghost"
                    :disabled="$activeFilterCount === 0"
                >
                    Reset semua filter
                </flux:button>
            </div>
        </div>

        <div wire:loading.flex wire:target="module_id,attempt_status,project_status,search,resetFilters" class="mt-4 items-center gap-2 text-sm font-medium text-elkm-primary" role="status">
            <span class="size-2 animate-pulse rounded-full bg-elkm-primary"></span>
            Memperbarui laporan…
        </div>
    </section>

    <section id="ringkasan" aria-labelledby="ringkasan-heading" class="scroll-mt-6 space-y-4">
        <div>
            <h2 id="ringkasan-heading" class="text-xl font-bold text-elkm-text">Ringkasan kinerja</h2>
            <p class="mt-1 text-sm text-elkm-muted">Angka utama untuk membantu Anda mengenali kondisi kelas dengan cepat.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-800">Attempt tuntas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-950">{{ $tuntasCount }}</p>
            </article>
            <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">Perlu remedial</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-amber-950">{{ $remedialCount }}</p>
            </article>
            <article class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-sm font-semibold text-blue-800">Rata-rata asesmen</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-blue-950">{{ $assessmentAverageScore ?? '-' }}</p>
            </article>
            <article class="rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-sm font-semibold text-violet-800">Proyek direview</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-violet-950">{{ $reviewedProjectCount }}</p>
            </article>
            <article class="rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:col-span-2 xl:col-span-1">
                <p class="text-sm font-semibold text-sky-800">Aktivitas diskusi</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-sky-950">{{ $discussionCount }}</p>
            </article>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <flux:card class="space-y-2">
                <p class="text-sm font-semibold text-elkm-muted">Respons diskusi guru</p>
                <p class="text-2xl font-bold tabular-nums">{{ $respondedDiscussionCount }}/{{ $discussionThreadCount }}</p>
                <p class="text-sm text-elkm-muted">{{ $unrespondedDiscussionCount }} thread belum direspons</p>
            </flux:card>
            <flux:card class="space-y-2">
                <p class="text-sm font-semibold text-elkm-muted">Rata-rata partisipasi</p>
                <p class="text-2xl font-bold tabular-nums">{{ $averageParticipationScore ?? '-' }}</p>
                <p class="text-sm text-elkm-muted">Dari diskusi yang sudah dinilai</p>
            </flux:card>
            <flux:card class="space-y-2">
                <p class="text-sm font-semibold text-elkm-muted">Rata-rata nilai proyek</p>
                <p class="text-2xl font-bold tabular-nums">{{ $reviewedProjectAverageScore ?? '-' }}</p>
                <p class="text-sm text-elkm-muted">Dari proyek yang sudah direview</p>
            </flux:card>
            <flux:card>
                <p class="text-sm font-semibold text-elkm-muted">Distribusi status proyek</p>
                <dl class="mt-3 space-y-2 text-sm">
                    @forelse ($projectStatusSummary as $projectStatus)
                        @php
                            $projectStatusLabel = match ($projectStatus->status) {
                                'submitted' => 'Dikirim',
                                'reviewed' => 'Sudah direview',
                                default => Illuminate\Support\Str::headline($projectStatus->status),
                            };
                        @endphp
                        <div class="flex justify-between gap-4" wire:key="project-status-summary-{{ $projectStatus->status }}">
                            <dt>{{ $projectStatusLabel }}</dt>
                            <dd class="font-bold tabular-nums">{{ $projectStatus->total }}</dd>
                        </div>
                    @empty
                        <div class="text-elkm-muted">Belum ada proyek.</div>
                    @endforelse
                </dl>
            </flux:card>
        </div>
    </section>

    <section id="attempt-asesmen" aria-labelledby="attempt-heading" class="scroll-mt-6 space-y-4">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 id="attempt-heading" class="text-xl font-bold text-elkm-text">Attempt Asesmen</h2>
                <p id="attempt-description" class="mt-1 text-sm text-elkm-muted">Maksimal 20 attempt terbaru yang sesuai dengan filter.</p>
            </div>
            <span class="text-sm font-semibold text-elkm-primary">{{ $attempts->count() }} data ditampilkan</span>
        </div>

        <div class="grid gap-3 md:hidden" aria-describedby="attempt-description">
            @forelse ($attempts as $attempt)
                @php
                    $attemptStatusColor = match ($attempt->status) {
                        'tuntas' => 'green',
                        'remedial' => 'yellow',
                        default => 'blue',
                    };
                @endphp
                <article wire:key="attempt-mobile-{{ $attempt->id }}" class="rounded-2xl border border-elkm-line bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-elkm-text">{{ $attempt->student->name }}</h3>
                            <p class="mt-1 text-sm leading-5 text-elkm-muted">{{ $attempt->assessment->title }}</p>
                        </div>
                        <flux:badge size="sm" color="{{ $attemptStatusColor }}">{{ Illuminate\Support\Str::headline($attempt->status) }}</flux:badge>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-elkm-line pt-3 text-sm">
                        <div>
                            <dt class="text-elkm-muted">Nilai</dt>
                            <dd class="mt-1 font-bold tabular-nums">{{ (float) $attempt->max_score > 0 ? $attempt->total_score.'/'.$attempt->max_score : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-elkm-muted">Percobaan</dt>
                            <dd class="mt-1 font-bold tabular-nums">Ke-{{ $attempt->attempt_number }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted">Belum ada attempt asesmen sesuai filter.</div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto rounded-2xl border border-elkm-line bg-white shadow-sm md:block" aria-describedby="attempt-description">
            <table class="min-w-full divide-y divide-elkm-line text-sm">
                <caption class="sr-only">Daftar attempt asesmen berdasarkan filter laporan saat ini</caption>
                <thead class="bg-elkm-surface text-left text-elkm-muted">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Murid</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Asesmen</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Nilai</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-elkm-line">
                    @forelse ($attempts as $attempt)
                        @php
                            $attemptStatusColor = match ($attempt->status) {
                                'tuntas' => 'green',
                                'remedial' => 'yellow',
                                default => 'blue',
                            };
                        @endphp
                        <tr wire:key="attempt-{{ $attempt->id }}" class="align-top transition hover:bg-elkm-surface/70">
                            <th scope="row" class="px-5 py-4 text-left font-semibold text-elkm-text">{{ $attempt->student->name }}</th>
                            <td class="max-w-xl px-5 py-4 leading-5">{{ $attempt->assessment->title }}</td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold tabular-nums">{{ (float) $attempt->max_score > 0 ? $attempt->total_score.'/'.$attempt->max_score : '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <flux:badge size="sm" color="{{ $attemptStatusColor }}">{{ Illuminate\Support\Str::headline($attempt->status) }}</flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-elkm-muted">Belum ada attempt asesmen sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section id="progres-remedial" aria-labelledby="progres-remedial-heading" class="scroll-mt-6 space-y-4">
        <div>
            <h2 id="progres-remedial-heading" class="text-xl font-bold text-elkm-text">Progres dan tindak lanjut</h2>
            <p class="mt-1 text-sm text-elkm-muted">Lihat perkembangan belajar dan murid yang memerlukan remedial.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section aria-labelledby="progres-heading" class="space-y-3">
                <h3 id="progres-heading" class="text-base font-bold text-elkm-text">Progres Belajar</h3>
                @forelse ($progressRecords as $progress)
                    <flux:card wire:key="progress-{{ $progress->id }}" class="space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <div class="font-bold">{{ $progress->user->name }}</div>
                                <div class="mt-1 text-sm text-elkm-muted">{{ $progress->module->title }}</div>
                            </div>
                            <flux:badge size="sm" color="{{ $progress->status === 'tuntas' ? 'green' : ($progress->status === 'remedial' ? 'yellow' : 'blue') }}">
                                {{ Illuminate\Support\Str::headline($progress->status) }}
                            </flux:badge>
                        </div>
                        <dl class="grid grid-cols-2 gap-3 border-t border-elkm-line pt-3 text-sm">
                            <div>
                                <dt class="text-elkm-muted">Kegiatan</dt>
                                <dd class="mt-1 font-semibold">{{ $progress->learningUnit?->title ?? 'Asesmen akhir' }}</dd>
                            </div>
                            <div>
                                <dt class="text-elkm-muted">Nilai</dt>
                                <dd class="mt-1 font-semibold tabular-nums">{{ $progress->score ?? '-' }}</dd>
                            </div>
                        </dl>
                    </flux:card>
                @empty
                    <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted">Belum ada progres sesuai filter.</div>
                @endforelse
            </section>

            <section aria-labelledby="remedial-heading" class="space-y-3">
                <h3 id="remedial-heading" class="text-base font-bold text-elkm-text">Remedial</h3>
                @forelse ($remedialAttempts as $attempt)
                    <flux:card wire:key="report-remedial-{{ $attempt->id }}" class="space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <div class="font-bold">{{ $attempt->student->name }}</div>
                                <div class="mt-1 text-sm leading-5 text-elkm-muted">{{ $attempt->assessment->title }}</div>
                            </div>
                            <flux:badge size="sm" color="yellow">Remedial</flux:badge>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-elkm-line pt-3 text-sm">
                            <span class="text-elkm-muted">{{ $attempt->assessment->module->title }}</span>
                            <span class="font-bold tabular-nums">{{ (float) $attempt->max_score > 0 ? $attempt->total_score.'/'.$attempt->max_score : '-' }}</span>
                        </div>
                    </flux:card>
                @empty
                    <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted">Tidak ada remedial sesuai filter.</div>
                @endforelse
            </section>
        </div>
    </section>

    <section id="proyek" aria-labelledby="proyek-heading" class="scroll-mt-6 space-y-4">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 id="proyek-heading" class="text-xl font-bold text-elkm-text">Proyek murid</h2>
                <p class="mt-1 text-sm text-elkm-muted">Pantau proyek yang masuk dan buka halaman review saat diperlukan.</p>
            </div>
            <flux:button size="sm" :href="route('guru.projects')" wire:navigate>Lihat semua proyek</flux:button>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($projects as $project)
                @php
                    $projectStatusLabel = match ($project->status) {
                        'submitted' => 'Dikirim',
                        'reviewed' => 'Sudah direview',
                        default => Illuminate\Support\Str::headline($project->status),
                    };
                @endphp
                <flux:card wire:key="report-project-{{ $project->id }}" class="space-y-4">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div>
                            <div class="font-bold">{{ $project->project_title }}</div>
                            <div class="mt-1 text-sm text-elkm-muted">{{ $project->user->name }} · {{ $project->module->title }}</div>
                        </div>
                        <flux:badge size="sm" color="{{ $project->status === 'reviewed' ? 'green' : ($project->status === 'submitted' ? 'blue' : 'zinc') }}">
                            {{ $projectStatusLabel }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center justify-between border-t border-elkm-line pt-3 text-sm">
                        <span class="text-elkm-muted">Nilai proyek</span>
                        <span class="font-bold tabular-nums">{{ $project->score ?? '-' }}</span>
                    </div>
                    @if ($project->rubricScores->isNotEmpty())
                        <dl class="grid gap-2 text-xs sm:grid-cols-2">
                            @foreach ($project->rubricScores as $rubricScore)
                                <div wire:key="report-project-rubric-{{ $rubricScore->id }}" class="flex justify-between gap-3 rounded-xl bg-elkm-surface px-3 py-2">
                                    <dt>{{ $rubricScore->criterion }}</dt>
                                    <dd class="font-bold tabular-nums">{{ $rubricScore->score }}/{{ $rubricScore->max_score }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </flux:card>
            @empty
                <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted lg:col-span-2">Belum ada proyek sesuai filter.</div>
            @endforelse
        </div>
    </section>

    <section id="diskusi" aria-labelledby="diskusi-heading" class="scroll-mt-6 space-y-4">
        <div>
            <h2 id="diskusi-heading" class="text-xl font-bold text-elkm-text">Diskusi dan partisipasi</h2>
            <p class="mt-1 text-sm text-elkm-muted">Prioritaskan thread yang belum direspons dan amati keterlibatan murid.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section aria-labelledby="diskusi-terbaru-heading" class="space-y-3">
                <h3 id="diskusi-terbaru-heading" class="text-base font-bold text-elkm-text">Diskusi terbaru</h3>
                @forelse ($discussions as $discussion)
                    @php($hasTeacherReply = $discussion->replies->contains(fn ($reply) => $reply->user->roles->contains('name', 'guru')))
                    <flux:card wire:key="report-discussion-{{ $discussion->id }}" class="space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <div class="font-bold">{{ $discussion->user->name }}</div>
                                <div class="mt-1 text-sm text-elkm-muted">{{ $discussion->learningUnit->title }}</div>
                            </div>
                            <flux:badge size="sm" color="{{ $hasTeacherReply ? 'green' : 'yellow' }}">{{ $hasTeacherReply ? 'Direspons guru' : 'Belum direspons guru' }}</flux:badge>
                        </div>
                        <p class="text-sm leading-6 text-elkm-text">{{ $discussion->body }}</p>
                    </flux:card>
                @empty
                    <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted">Belum ada diskusi sesuai filter.</div>
                @endforelse
            </section>

            <section aria-labelledby="partisipasi-heading" class="space-y-3">
                <h3 id="partisipasi-heading" class="text-base font-bold text-elkm-text">Partisipasi diskusi</h3>
                @forelse ($discussionParticipation as $participant)
                    <flux:card wire:key="discussion-participation-{{ $participant->user_id }}">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold">{{ $participant->user->name }}</div>
                                <div class="mt-1 text-sm text-elkm-muted">{{ $participant->total_discussions }} diskusi dan balasan</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold uppercase tracking-wide text-elkm-muted">Rata-rata</div>
                                <div class="mt-1 text-xl font-bold tabular-nums">{{ $participant->average_participation_score ? round((float) $participant->average_participation_score, 2) : '-' }}</div>
                            </div>
                        </div>
                    </flux:card>
                @empty
                    <div class="rounded-2xl border border-dashed border-elkm-line bg-white p-6 text-center text-sm text-elkm-muted">Belum ada partisipasi diskusi.</div>
                @endforelse
            </section>
        </div>
    </section>
</div>
