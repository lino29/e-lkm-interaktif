<?php

namespace App\Livewire\Guru;

use App\Models\AssessmentAttempt;
use Livewire\Component;
use Livewire\WithPagination;

class ManageRemedials extends Component
{
    use WithPagination;

    public function render()
    {
        $query = AssessmentAttempt::with(['assessment.module', 'assessment.learningUnit', 'student'])
            ->where('status', 'remedial')
            ->whereHas('assessment.module', function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->latest('submitted_at');

        $paginator = $query->paginate(10);

        $paginator->getCollection()->transform(function ($attempt) {
            $attemptsUsed = AssessmentAttempt::where('assessment_id', $attempt->assessment_id)
                ->where('student_id', $attempt->student_id)
                ->count();

            $attempt->attempts_used = $attemptsUsed;
            $attempt->remaining_attempts = max(0, $attempt->assessment->max_attempts - $attemptsUsed);

            return $attempt;
        });

        return view('livewire.guru.manage-remedials', [
            'attempts' => $paginator,
        ]);
    }
}
