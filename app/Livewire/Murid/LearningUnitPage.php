<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Services\Learning\LearningUnitOutlineService;
use App\Services\Learning\ProgressService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LearningUnitPage extends Component
{
    public LearningUnit $currentLearningUnit;

    public ?int $activeSectionId = null;

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::with([
            'module',
            'rootSections.children.media',
            'sections.children.media',
            'sections.media',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ])
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);

        if ($this->currentLearningUnit->sections()->count() === 0) {
            app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        }
        $this->currentLearningUnit = $this->currentLearningUnit->fresh([
            'module',
            'rootSections.children.media',
            'sections.children.media',
            'sections.media',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ]);

        app(ProgressService::class)->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);

        $this->activeSectionId = $this->currentLearningUnit->rootSections->first()?->id;
    }

    public function openSection(int $sectionId): void
    {
        $exists = $this->currentLearningUnit
            ->sections()
            ->whereKey($sectionId)
            ->exists();

        if ($exists) {
            $this->activeSectionId = $sectionId;
        }
    }

    #[Computed]
    public function activeSection(): ?LearningUnitSection
    {
        if (! $this->activeSectionId) {
            return $this->currentLearningUnit->rootSections->first();
        }

        return $this->currentLearningUnit
            ->sections()
            ->with(['children.media', 'media'])
            ->find($this->activeSectionId);
    }

    private function getActivityStatuses(): array
    {
        $answers = ActivityAnswer::where('user_id', auth()->id())
            ->whereIn('activity_id', $this->currentLearningUnit->activities->pluck('id'))
            ->get()
            ->keyBy('activity_id');

        $statuses = [];
        foreach ($this->currentLearningUnit->activities as $activity) {
            $answer = $answers->get($activity->id);
            $status = $answer ? $answer->status : 'belum_mulai';

            $statuses[$activity->id] = [
                'status' => $status,
                'answer' => $answer,
            ];
        }

        return $statuses;
    }

    private function getAssessmentStatuses(): array
    {
        $assessments = $this->currentLearningUnit->assessments;
        $statuses = [];
        foreach ($assessments as $assessment) {
            $latestAttempt = AssessmentAttempt::where('assessment_id', $assessment->id)
                ->where('student_id', auth()->id())
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->first();

            $statuses[$assessment->id] = [
                'status' => $latestAttempt?->status ?? 'belum_mulai',
            ];
        }

        return $statuses;
    }

    #[Computed]
    public function flatVisibleSections()
    {
        $flatten = function ($sections) use (&$flatten) {
            $result = collect();
            foreach ($sections as $section) {
                if ($section->is_visible) {
                    $result->push($section);
                    if ($section->children->isNotEmpty()) {
                        $result = $result->concat($flatten($section->children));
                    }
                }
            }

            return $result;
        };

        return $flatten($this->currentLearningUnit->rootSections);
    }

    #[Computed]
    public function previousSection(): ?LearningUnitSection
    {
        $flat = $this->flatVisibleSections();
        $index = $flat->search(fn ($s) => $s->id === $this->activeSectionId);

        return $index !== false && $index > 0 ? $flat[$index - 1] : null;
    }

    #[Computed]
    public function nextSection(): ?LearningUnitSection
    {
        $flat = $this->flatVisibleSections();
        $index = $flat->search(fn ($s) => $s->id === $this->activeSectionId);

        return $index !== false && $index < $flat->count() - 1 ? $flat[$index + 1] : null;
    }

    public function render()
    {
        return view('livewire.murid.learning-unit-page', [
            'learningUnit' => $this->currentLearningUnit,
            'activityStatuses' => $this->getActivityStatuses(),
            'assessmentStatuses' => $this->getAssessmentStatuses(),
        ]);
    }
}
