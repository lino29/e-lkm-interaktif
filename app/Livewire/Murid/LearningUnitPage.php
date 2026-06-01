<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
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
            'rootSections.children',
            'sections.children',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ])
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);

        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        $this->currentLearningUnit = $this->currentLearningUnit->fresh([
            'module',
            'rootSections.children',
            'sections.children',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ]);

        $progressService = app(ProgressService::class);

        abort_unless($progressService->isLearningUnitUnlocked(auth()->user(), $this->currentLearningUnit), 403);

        $progressService->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);

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
            ->with('children')
            ->find($this->activeSectionId);
    }

    private function getActivityStatuses(): array
    {
        $answers = ActivityAnswer::where('user_id', auth()->id())
            ->whereIn('activity_id', $this->currentLearningUnit->activities->pluck('id'))
            ->get()
            ->keyBy('activity_id');

        $statuses = [];
        $progressService = app(ProgressService::class);

        foreach ($this->currentLearningUnit->activities as $activity) {
            $answer = $answers->get($activity->id);
            $status = $answer ? $answer->status : 'belum_mulai';

            $statuses[$activity->id] = [
                'status' => $status,
                'is_locked' => ! $progressService->isActivityUnlocked(auth()->user(), $activity),
                'answer' => $answer,
            ];
        }

        return $statuses;
    }

    public function render()
    {
        return view('livewire.murid.learning-unit-page', [
            'learningUnit' => $this->currentLearningUnit,
            'activityStatuses' => $this->getActivityStatuses(),
        ]);
    }
}
