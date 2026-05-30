<div class="space-y-6">
    <flux:heading size="xl">Portofolio Belajar</flux:heading>
    <section class="space-y-3">
        <flux:heading>Jawaban Aktivitas</flux:heading>
        @foreach ($activityAnswers as $answer)
            <flux:card wire:key="portfolio-answer-{{ $answer->id }}"><div class="font-semibold">{{ $answer->activity->title }}</div><flux:text>{{ $answer->activity->learningUnit->title }}</flux:text><p class="mt-2 text-sm">{{ $answer->answer_text }}</p></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Proyek</flux:heading>
        @foreach ($projects as $project)
            <flux:card wire:key="portfolio-project-{{ $project->id }}">
                <div class="font-semibold">{{ $project->project_title }}</div>
                <flux:text>{{ $project->module->title }} - {{ $project->status }} - Nilai {{ $project->score ?? '-' }}</flux:text>
                @if ($project->conclusion)
                    <p class="mt-2 text-sm">{{ $project->conclusion }}</p>
                @endif
                @if ($project->feedback)
                    <div class="mt-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-800">
                        <span class="font-medium">Feedback guru:</span> {{ $project->feedback }}
                    </div>
                @endif
            </flux:card>
        @endforeach
    </section>
</div>
