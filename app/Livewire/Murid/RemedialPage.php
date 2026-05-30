<?php

namespace App\Livewire\Murid;

use App\Models\AssessmentAttempt;
use Livewire\Component;

class RemedialPage extends Component
{
    public function render()
    {
        $attempts = AssessmentAttempt::with('assessment.module', 'assessment.learningUnit')
            ->where('student_id', auth()->id())
            ->where('status', 'remedial')
            ->whereHas('assessment', fn ($query) => $query->where('is_published', true))
            ->whereHas('assessment.module', fn ($query) => $query->where('status', 'published'))
            ->latest('submitted_at')
            ->get()
            ->unique('assessment_id')
            ->values();

        return view('livewire.murid.remedial-page', [
            'remedials' => $attempts->map(fn (AssessmentAttempt $attempt): array => [
                'attempt' => $attempt,
                'assessment' => $attempt->assessment,
                'attemptsUsed' => AssessmentAttempt::where('assessment_id', $attempt->assessment_id)
                    ->where('student_id', auth()->id())
                    ->count(),
                'remainingAttempts' => max(0, $attempt->assessment->max_attempts - AssessmentAttempt::where('assessment_id', $attempt->assessment_id)
                    ->where('student_id', auth()->id())
                    ->count()),
                'recommendation' => $attempt->assessment->learningUnit
                    ? 'Pelajari ulang kegiatan '.$attempt->assessment->learningUnit->title.' sebelum mencoba lagi.'
                    : 'Pelajari ulang materi pada modul '.$attempt->assessment->module->title.' sebelum mencoba lagi.',
            ]),
        ]);
    }
}
