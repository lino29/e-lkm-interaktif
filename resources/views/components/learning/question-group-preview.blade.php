@props(['section'])

@php
    $assessmentGroup = $section->parent;
    $assessment = $assessmentGroup?->linkedModel();
    $questions = $assessment
        ? $assessment->questions->filter(fn ($question) => ($question->question_group ?? app(\App\Services\Assessment\QuestionGroupService::class)->groupForType($question->question_type)) === $section->slug)
        : collect();
@endphp

<div class="space-y-3">
    @forelse ($questions as $question)
        <div class="card-elkm p-4 text-sm" wire:key="outline-question-{{ $question->id }}">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div class="font-bold leading-relaxed text-elkm-text">{{ $question->question_text }}</div>
                <span class="w-fit shrink-0 rounded-full border border-elkm-line bg-elkm-surface px-2.5 py-1 text-xs font-semibold text-elkm-muted">
                    {{ \Illuminate\Support\Str::headline($question->question_type) }}
                </span>
            </div>

            <x-learning.question-answer-preview :question="$question" />
        </div>
    @empty
        <div class="card-elkm p-4 text-sm text-elkm-muted">Belum ada soal pada kelompok ini.</div>
    @endforelse
</div>
