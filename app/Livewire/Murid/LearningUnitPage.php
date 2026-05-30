<?php

namespace App\Livewire\Murid;

use App\Models\LearningUnit;
use App\Services\Learning\ProgressService;
use Livewire\Component;

class LearningUnitPage extends Component
{
    public LearningUnit $currentLearningUnit;

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::with('module', 'materials', 'activities', 'assessments')
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);
        app(ProgressService::class)->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);
    }

    public function render()
    {
        return view('livewire.murid.learning-unit-page', [
            'learningUnit' => $this->currentLearningUnit,
        ]);
    }
}
