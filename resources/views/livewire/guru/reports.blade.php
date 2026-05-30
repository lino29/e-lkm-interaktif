<div class="space-y-6">
    <flux:heading size="xl">Laporan Guru</flux:heading>

    <div class="flex items-center space-x-4">
        <flux:select wire:model.live="module_id" placeholder="Semua Modul" class="max-w-xs">
            <flux:select.option value="">Semua Modul</flux:select.option>
            @foreach($modules as $module)
                <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <flux:card>
            <div class="text-sm text-zinc-500">Total Siswa Tuntas</div>
            <div class="text-2xl font-semibold">{{ $tuntasCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Total Siswa Remedial</div>
            <div class="text-2xl font-semibold">{{ $remedialCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Proyek Masuk</div>
            <div class="text-2xl font-semibold">{{ $submittedProjectCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Proyek Reviewed</div>
            <div class="text-2xl font-semibold">{{ $reviewedProjectCount }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Diskusi</div>
            <div class="text-2xl font-semibold">{{ $discussionCount }}</div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <flux:card>
            <div class="text-sm text-zinc-500">Diskusi Direspons Guru</div>
            <div class="text-2xl font-semibold">{{ $respondedDiscussionCount }}/{{ $discussionThreadCount }}</div>
            <flux:text>{{ $unrespondedDiscussionCount }} thread belum direspons guru</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Rata-rata Partisipasi</div>
            <div class="text-2xl font-semibold">{{ $averageParticipationScore ?? '-' }}</div>
            <flux:text>Dari diskusi yang sudah dinilai</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Rata-rata Nilai Proyek</div>
            <div class="text-2xl font-semibold">{{ $reviewedProjectAverageScore ?? '-' }}</div>
            <flux:text>Dihitung dari proyek berstatus reviewed</flux:text>
        </flux:card>
        <flux:card>
            <div class="text-sm text-zinc-500">Status Proyek</div>
            <div class="mt-2 space-y-1 text-sm">
                @foreach ($projectStatusSummary as $projectStatus)
                    <div class="flex justify-between" wire:key="project-status-summary-{{ $projectStatus->status }}">
                        <span class="capitalize text-zinc-500">{{ $projectStatus->status }}</span>
                        <span class="font-semibold">{{ $projectStatus->total }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>

    <section class="space-y-3">
        <flux:heading>Attempt Asesmen Terbaru</flux:heading>
        @foreach ($attempts as $attempt)
            <flux:card wire:key="attempt-{{ $attempt->id }}"><div class="font-semibold">{{ $attempt->student->name }} - {{ $attempt->assessment->title }}</div><flux:text>{{ $attempt->total_score }}/{{ $attempt->max_score }} - {{ $attempt->status }}</flux:text></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Progress Belajar</flux:heading>
        @foreach ($progressRecords as $progress)
            <flux:card wire:key="progress-{{ $progress->id }}"><div class="font-semibold">{{ $progress->user->name }} - {{ $progress->module->title }}</div><flux:text>{{ $progress->learningUnit?->title ?? 'Asesmen akhir' }} - {{ $progress->status }}</flux:text></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Remedial</flux:heading>
        @foreach ($remedialAttempts as $attempt)
            <flux:card wire:key="report-remedial-{{ $attempt->id }}"><div class="font-semibold">{{ $attempt->student->name }} - {{ $attempt->assessment->title }}</div><flux:text>{{ $attempt->assessment->module->title }} - {{ $attempt->total_score }}/{{ $attempt->max_score }}</flux:text></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Proyek Masuk</flux:heading>
        @foreach ($projects as $project)
            <flux:card wire:key="report-project-{{ $project->id }}"><div class="font-semibold">{{ $project->user->name }} - {{ $project->project_title }}</div><flux:text>{{ $project->module->title }} - {{ $project->status }} - Nilai {{ $project->score ?? '-' }}</flux:text>
                @if ($project->rubricScores->isNotEmpty())
                    <div class="mt-2 grid gap-2 text-xs md:grid-cols-2">
                        @foreach ($project->rubricScores as $rubricScore)
                            <div wire:key="report-project-rubric-{{ $rubricScore->id }}" class="flex justify-between rounded bg-zinc-50 px-2 py-1 dark:bg-zinc-800">
                                <span>{{ $rubricScore->criterion }}</span>
                                <span class="font-medium">{{ $rubricScore->score }}/{{ $rubricScore->max_score }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Diskusi Terbaru</flux:heading>
        @foreach ($discussions as $discussion)
            @php($hasTeacherReply = $discussion->replies->contains(fn ($reply) => $reply->user->hasRole('guru')))
            <flux:card wire:key="report-discussion-{{ $discussion->id }}"><div class="font-semibold">{{ $discussion->user->name }} - {{ $discussion->learningUnit->title }}</div><flux:badge size="sm" color="{{ $hasTeacherReply ? 'green' : 'yellow' }}">{{ $hasTeacherReply ? 'Direspons guru' : 'Belum direspons guru' }}</flux:badge><p class="mt-2 text-sm">{{ $discussion->body }}</p></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Partisipasi Diskusi</flux:heading>
        @foreach ($discussionParticipation as $participant)
            <flux:card wire:key="discussion-participation-{{ $participant->user_id }}"><div class="font-semibold">{{ $participant->user->name }}</div><flux:text>{{ $participant->total_discussions }} diskusi dan reply - rata-rata skor {{ $participant->average_participation_score ? round((float) $participant->average_participation_score, 2) : '-' }}</flux:text></flux:card>
        @endforeach
    </section>
</div>
