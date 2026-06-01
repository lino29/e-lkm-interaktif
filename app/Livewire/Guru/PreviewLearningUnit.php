<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Services\Learning\LearningUnitOutlineService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PreviewLearningUnit extends Component
{
    public LearningUnit $currentLearningUnit;

    public ?int $activeSectionId = null;

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::query()
            ->when(! auth()->user()->hasRole('admin'), fn ($query) => $query->whereHas('module', fn ($moduleQuery) => $moduleQuery->where('created_by', auth()->id())))
            ->findOrFail($learningUnit);

        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
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

        $this->activeSectionId = $this->currentLearningUnit->rootSections->where('is_visible', true)->first()?->id;
    }

    public function openSection(int $sectionId): void
    {
        $exists = $this->currentLearningUnit
            ->sections()
            ->whereKey($sectionId)
            ->where('is_visible', true)
            ->exists();

        if ($exists) {
            $this->activeSectionId = $sectionId;
        }
    }

    #[Computed]
    public function activeSection(): ?LearningUnitSection
    {
        if (! $this->activeSectionId) {
            return $this->currentLearningUnit->rootSections->where('is_visible', true)->first();
        }

        return $this->currentLearningUnit
            ->sections()
            ->with(['children.media', 'media'])
            ->find($this->activeSectionId);
    }

    public function render()
    {
        return view('livewire.guru.preview-learning-unit', [
            'learningUnit' => $this->currentLearningUnit,
            'activityStatuses' => [],
        ]);
    }
}
