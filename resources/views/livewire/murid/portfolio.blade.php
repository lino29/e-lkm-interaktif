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
            <flux:card wire:key="portfolio-project-{{ $project->id }}"><div class="font-semibold">{{ $project->project_title }}</div><flux:text>{{ $project->module->title }} · {{ $project->status }}</flux:text></flux:card>
        @endforeach
    </section>
</div>
