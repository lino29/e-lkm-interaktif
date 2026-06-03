<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Laporan</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">Laporan Guru</flux:heading>
            <flux:text>Pantau ketuntasan asesmen, progres kegiatan belajar, proyek, remedial, dan diskusi murid.</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="exportExcel" icon="document-text" variant="primary">Export Excel</flux:button>
            <flux:button wire:click="exportPdf" icon="document-arrow-down">Export PDF</flux:button>
        </div>
    </div>

    <flux:card class="space-y-4">
        <div class="grid gap-4 md:grid-cols-4">
            <flux:field>
                <flux:label>Modul</flux:label>
                <flux:select wire:model.live="module_id">
                    <flux:select.option value="">Semua Modul</flux:select.option>
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
                    <flux:select.option value="started">Sedang dikerjakan</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Status proyek</flux:label>
                <flux:select wire:model.live="project_status">
                    <flux:select.option value="">Semua status</flux:select.option>
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="submitted">Submitted</flux:select.option>
                    <flux:select.option value="reviewed">Reviewed</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Cari</flux:label>
                <flux:input wire:model.live.debounce.400ms="search" placeholder="Nama murid, judul proyek, isi diskusi" />
            </flux:field>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <flux:card>
            <div class="text-sm text-zinc-500">Siswa Tuntas</div>
            <div class="mt-1 text-2xl font-semibold">{{ $tuntasCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Siswa Remedial</div>
            <div class="mt-1 text-2xl font-semibold">{{ $remedialCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Rata-rata Asesmen</div>
            <div class="mt-1 text-2xl font-semibold">{{ $assessmentAverageScore ?? '-' }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Proyek Reviewed</div>
            <div class="mt-1 text-2xl font-semibold">{{ $reviewedProjectCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Diskusi</div>
            <div class="mt-1 text-2xl font-semibold">{{ $discussionCount }}</div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <flux:card>
            <div class="text-sm text-zinc-500">Diskusi Direspons Guru</div>
            <div class="mt-1 text-2xl font-semibold">{{ $respondedDiscussionCount }}/{{ $discussionThreadCount }}</div>
            <flux:text>{{ $unrespondedDiscussionCount }} thread belum direspons guru</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Rata-rata Partisipasi</div>
            <div class="mt-1 text-2xl font-semibold">{{ $averageParticipationScore ?? '-' }}</div>
            <flux:text>Dari diskusi yang sudah dinilai</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Rata-rata Nilai Proyek</div>
            <div class="mt-1 text-2xl font-semibold">{{ $reviewedProjectAverageScore ?? '-' }}</div>
            <flux:text>Dihitung dari proyek reviewed</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Status Proyek</div>
            <div class="mt-2 space-y-1 text-sm">
                @forelse ($projectStatusSummary as $projectStatus)
                    <div class="flex justify-between" wire:key="project-status-summary-{{ $projectStatus->status }}">
                        <span class="capitalize text-zinc-500">{{ $projectStatus->status }}</span>
                        <span class="font-semibold">{{ $projectStatus->total }}</span>
                    </div>
                @empty
                    <span class="text-zinc-500">Belum ada proyek.</span>
                @endforelse
            </div>
        </flux:card>
    </div>

    <section class="space-y-3">
        <flux:heading>Attempt Asesmen</flux:heading>
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3">Murid</th>
                        <th class="px-4 py-3">Asesmen</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($attempts as $attempt)
                        <tr wire:key="attempt-{{ $attempt->id }}">
                            <td class="px-4 py-3 font-medium">{{ $attempt->student->name }}</td>
                            <td class="px-4 py-3">{{ $attempt->assessment->title }}</td>
                            <td class="px-4 py-3">{{ $attempt->total_score }}/{{ $attempt->max_score }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $attempt->status === 'tuntas' ? 'green' : 'yellow' }}">{{ $attempt->status }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">{{ $attempt->submitted_at?->diffForHumans() ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">Belum ada attempt asesmen sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-3">
            <flux:heading>Progress Belajar</flux:heading>
            @forelse ($progressRecords as $progress)
                <flux:card wire:key="progress-{{ $progress->id }}">
                    <div class="font-semibold">{{ $progress->user->name }} - {{ $progress->module->title }}</div>
                    <flux:text>{{ $progress->learningUnit?->title ?? 'Asesmen akhir' }} - {{ $progress->status }} - Nilai {{ $progress->score ?? '-' }}</flux:text>
                </flux:card>
            @empty
                <flux:card><flux:text>Belum ada progress.</flux:text></flux:card>
            @endforelse
        </section>

        <section class="space-y-3">
            <flux:heading>Remedial</flux:heading>
            @forelse ($remedialAttempts as $attempt)
                <flux:card wire:key="report-remedial-{{ $attempt->id }}">
                    <div class="font-semibold">{{ $attempt->student->name }} - {{ $attempt->assessment->title }}</div>
                    <flux:text>{{ $attempt->assessment->module->title }} - {{ $attempt->total_score }}/{{ $attempt->max_score }}</flux:text>
                </flux:card>
            @empty
                <flux:card><flux:text>Tidak ada remedial pada filter ini.</flux:text></flux:card>
            @endforelse
        </section>
    </div>

    <section class="space-y-3">
        <flux:heading>Proyek Masuk</flux:heading>
        @forelse ($projects as $project)
            <flux:card wire:key="report-project-{{ $project->id }}" class="space-y-2">
                <div class="flex flex-col justify-between gap-2 md:flex-row md:items-start">
                    <div>
                        <div class="font-semibold">{{ $project->user->name }} - {{ $project->project_title }}</div>
                        <flux:text>{{ $project->module->title }} - {{ $project->status }} - Nilai {{ $project->score ?? '-' }}</flux:text>
                    </div>
                    <flux:button size="sm" :href="route('guru.projects')" wire:navigate>Review Proyek</flux:button>
                </div>
                @if ($project->rubricScores->isNotEmpty())
                    <div class="grid gap-2 text-xs md:grid-cols-2">
                        @foreach ($project->rubricScores as $rubricScore)
                            <div wire:key="report-project-rubric-{{ $rubricScore->id }}" class="flex justify-between rounded bg-zinc-50 px-2 py-1 dark:bg-zinc-800">
                                <span>{{ $rubricScore->criterion }}</span>
                                <span class="font-medium">{{ $rubricScore->score }}/{{ $rubricScore->max_score }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        @empty
            <flux:card><flux:text>Belum ada proyek sesuai filter.</flux:text></flux:card>
        @endforelse
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="space-y-3">
            <flux:heading>Diskusi Terbaru</flux:heading>
            @forelse ($discussions as $discussion)
                @php($hasTeacherReply = $discussion->replies->contains(fn ($reply) => $reply->user->hasRole('guru')))
                <flux:card wire:key="report-discussion-{{ $discussion->id }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="font-semibold">{{ $discussion->user->name }} - {{ $discussion->learningUnit->title }}</div>
                        <flux:badge size="sm" color="{{ $hasTeacherReply ? 'green' : 'yellow' }}">{{ $hasTeacherReply ? 'Direspons guru' : 'Belum direspons guru' }}</flux:badge>
                    </div>
                    <p class="mt-2 text-sm leading-6">{{ $discussion->body }}</p>
                </flux:card>
            @empty
                <flux:card><flux:text>Belum ada diskusi sesuai filter.</flux:text></flux:card>
            @endforelse
        </section>

        <section class="space-y-3">
            <flux:heading>Partisipasi Diskusi</flux:heading>
            @forelse ($discussionParticipation as $participant)
                <flux:card wire:key="discussion-participation-{{ $participant->user_id }}">
                    <div class="font-semibold">{{ $participant->user->name }}</div>
                    <flux:text>{{ $participant->total_discussions }} diskusi dan reply - rata-rata skor {{ $participant->average_participation_score ? round((float) $participant->average_participation_score, 2) : '-' }}</flux:text>
                </flux:card>
            @empty
                <flux:card><flux:text>Belum ada partisipasi diskusi.</flux:text></flux:card>
            @endforelse
        </section>
    </div>
</div>
