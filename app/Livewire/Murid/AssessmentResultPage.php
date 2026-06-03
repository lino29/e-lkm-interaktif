<?php

namespace App\Livewire\Murid;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Livewire\Component;

class AssessmentResultPage extends Component
{
    public Assessment $currentAssessment;

    public ?AssessmentAttempt $latestAttempt = null;

    public function mount(string|int $assessment): void
    {
        $this->currentAssessment = Assessment::findOrFail($assessment);

        $this->latestAttempt = AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->first();

        if (! $this->latestAttempt) {
            redirect()->route('murid.assessments.show', $this->currentAssessment->id);
        }
    }

    public function render()
    {
        return view('livewire.murid.assessment-result-page', [
            'assessment' => $this->currentAssessment,
            'attemptCount' => AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
                ->where('student_id', auth()->id())
                ->count(),
            'canRetake' => AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
                ->where('student_id', auth()->id())
                ->count() < $this->currentAssessment->max_attempts && $this->latestAttempt?->status !== 'tuntas',
        ]);
    }
}
