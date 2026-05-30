<div class="space-y-6">
    <flux:heading size="xl">Laporan Guru</flux:heading>
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
            <flux:card wire:key="report-project-{{ $project->id }}"><div class="font-semibold">{{ $project->user->name }} - {{ $project->project_title }}</div><flux:text>{{ $project->module->title }} - {{ $project->status }} - Nilai {{ $project->score ?? '-' }}</flux:text></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Diskusi Terbaru</flux:heading>
        @foreach ($discussions as $discussion)
            <flux:card wire:key="report-discussion-{{ $discussion->id }}"><div class="font-semibold">{{ $discussion->user->name }} - {{ $discussion->learningUnit->title }}</div><p class="mt-2 text-sm">{{ $discussion->body }}</p></flux:card>
        @endforeach
    </section>
</div>
