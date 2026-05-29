<?php

namespace App\Livewire\Murid;

use App\Models\LearningUnit;
use App\Services\Learning\ProgressService;
use Livewire\Component;

class LearningUnitPage extends Component
{
    public LearningUnit $learningUnit;

    public function mount(string|int $learningUnit): void
    {
        $this->learningUnit = LearningUnit::with('module', 'materials', 'activities', 'assessments')->findOrFail($learningUnit);
        app(ProgressService::class)->markStarted(auth()->user(), $this->learningUnit->module, $this->learningUnit);
    }

    public function render()
    {
        return view('livewire.murid.learning-unit-page');
    }
}
