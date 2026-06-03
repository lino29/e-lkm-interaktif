<?php

namespace App\Livewire\Murid;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\StudentAnswer;
use Livewire\Component;

class AssessmentReviewPage extends Component
{
    public Assessment $currentAssessment;

    public ?AssessmentAttempt $latestAttempt = null;

    public array $studentAnswers = [];

    public function mount(string|int $assessment): void
    {
        $this->currentAssessment = Assessment::with('questions')->findOrFail($assessment);

        $this->latestAttempt = AssessmentAttempt::with('studentAnswers.question')
            ->where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->first();

        if (! $this->latestAttempt) {
            redirect()->route('murid.assessments.show', $this->currentAssessment->id);

            return;
        }

        $this->studentAnswers = $this->latestAttempt->studentAnswers->keyBy('question_id')->all();
    }

    public function getStudentAnswer(int $questionId): ?StudentAnswer
    {
        return $this->studentAnswers[$questionId] ?? null;
    }

    public function render()
    {
        return view('livewire.murid.assessment-review-page', [
            'assessment' => $this->currentAssessment,
            'attempt' => $this->latestAttempt,
            'questions' => $this->currentAssessment->questions->sortBy('order'),
        ]);
    }
}
