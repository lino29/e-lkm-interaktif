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
        <div class="rounded-lg border p-4 text-sm" wire:key="outline-question-{{ $question->id }}">
            <div class="font-semibold">{{ $question->question_text }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ \Illuminate\Support\Str::headline($question->question_type) }}</div>
        </div>
    @empty
        <div class="rounded-lg border p-4 text-sm text-zinc-500">Belum ada soal pada kelompok ini.</div>
    @endforelse
</div>
