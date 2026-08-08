<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnitGrade;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class MyScores extends Component
{
    public string $activeTab = 'summative';

    public ?int $selectedAssessmentAttemptId = null;

    public ?int $selectedLearningUnitGradeId = null;

    public function showTab(string $tab): void
    {
        abort_unless(in_array($tab, ['summative', 'kb'], true), 404);

        $this->activeTab = $tab;
        $this->selectedAssessmentAttemptId = null;
        $this->selectedLearningUnitGradeId = null;
    }

    public function showAssessmentDetail(int $attemptId): void
    {
        $this->summativeAttemptQuery()->findOrFail($attemptId);
        $this->selectedAssessmentAttemptId = $attemptId;
    }

    public function showLearningUnitDetail(int $gradeId): void
    {
        $this->learningUnitGradeQuery()->findOrFail($gradeId);
        $this->selectedLearningUnitGradeId = $gradeId;
    }

    public function closeDetail(): void
    {
        $this->selectedAssessmentAttemptId = null;
        $this->selectedLearningUnitGradeId = null;
    }

    public function render()
    {
        [$semesterStart, $semesterEnd, $semesterLabel] = $this->semesterPeriod();

        $attempts = $this->summativeAttemptQuery()
            ->with('assessment.module:id,title')
            ->latest('submitted_at')
            ->get();

        $learningUnitGrades = $this->learningUnitGradeQuery()
            ->with(['learningUnit:id,module_id,title,order', 'learningUnit.module:id,title'])
            ->latest('reviewed_at')
            ->get();

        $selectedAttempt = $this->selectedAssessmentAttempt();
        $selectedLearningUnitGrade = $this->selectedLearningUnitGrade();

        return view('livewire.murid.my-scores', [
            'attempts' => $attempts,
            'learningUnitGrades' => $learningUnitGrades,
            'selectedAttempt' => $selectedAttempt,
            'selectedLearningUnitGrade' => $selectedLearningUnitGrade,
            'selectedLearningUnitAnswers' => $this->selectedLearningUnitAnswers($selectedLearningUnitGrade),
            'semesterLabel' => $semesterLabel,
            'semesterStart' => $semesterStart,
            'semesterEnd' => $semesterEnd,
        ]);
    }

    private function summativeAttemptQuery(): Builder
    {
        [$semesterStart, $semesterEnd] = $this->semesterPeriod();

        return AssessmentAttempt::query()
            ->where('student_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$semesterStart, $semesterEnd])
            ->whereHas('assessment', fn (Builder $query) => $query->where('type', 'final'));
    }

    private function learningUnitGradeQuery(): Builder
    {
        [$semesterStart, $semesterEnd] = $this->semesterPeriod();

        return LearningUnitGrade::query()
            ->where('student_id', auth()->id())
            ->whereBetween('created_at', [$semesterStart, $semesterEnd]);
    }

    private function selectedAssessmentAttempt(): ?AssessmentAttempt
    {
        if ($this->selectedAssessmentAttemptId === null) {
            return null;
        }

        return $this->summativeAttemptQuery()
            ->with([
                'assessment.module:id,title',
                'assessment.questions' => fn ($query) => $query->orderBy('order'),
                'studentAnswers.question',
            ])
            ->find($this->selectedAssessmentAttemptId);
    }

    private function selectedLearningUnitGrade(): ?LearningUnitGrade
    {
        if ($this->selectedLearningUnitGradeId === null) {
            return null;
        }

        return $this->learningUnitGradeQuery()
            ->with(['learningUnit.module:id,title', 'reviewer:id,name'])
            ->find($this->selectedLearningUnitGradeId);
    }

    /** @return Collection<int, ActivityAnswer> */
    private function selectedLearningUnitAnswers(?LearningUnitGrade $grade): Collection
    {
        if (! $grade) {
            return collect();
        }

        return ActivityAnswer::query()
            ->with('activity:id,learning_unit_id,title,phase,prompt,order')
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'draft')
            ->whereHas('activity', fn (Builder $query) => $query->where('learning_unit_id', $grade->learning_unit_id))
            ->orderBy('activity_id')
            ->get();
    }

    /** @return array{CarbonInterface, CarbonInterface, string} */
    private function semesterPeriod(): array
    {
        $today = now();

        if ($today->month >= 7) {
            $start = $today->copy()->startOfYear()->month(7)->startOfDay();
            $end = $today->copy()->endOfYear()->endOfDay();
            $label = 'Semester Ganjil '.$today->year.'/'.($today->year + 1);

            return [$start, $end, $label];
        }

        $start = $today->copy()->subYear()->startOfYear()->month(7)->startOfDay();
        $end = $today->copy()->startOfYear()->month(6)->endOfMonth()->endOfDay();
        $label = 'Semester Genap '.($today->year - 1).'/'.$today->year;

        return [$start, $end, $label];
    }
}
