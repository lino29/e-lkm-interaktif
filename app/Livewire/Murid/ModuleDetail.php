<?php

namespace App\Livewire\Murid;

use App\Models\Module;
use App\Services\Learning\ProgressService;
use Livewire\Component;

class ModuleDetail extends Component
{
    public Module $currentModule;

    public function mount(string|int $module): void
    {
        $this->currentModule = Module::where('status', 'published')->findOrFail($module);
        app(ProgressService::class)->markStarted(auth()->user(), $this->currentModule);
    }

    public function render()
    {
        $progressService = app(ProgressService::class);
        $student = auth()->user();
        $module = $this->currentModule->load([
            'subject',
            'learningUnits.materials',
            'learningUnits.activities.answers',
            'learningUnits.assessments' => fn ($query) => $query->where('is_published', true),
            'assessments' => fn ($query) => $query->where('is_published', true),
            'glossaries',
            'references',
        ]);

        return view('livewire.murid.module-detail', [
            'module' => $module,
            'completedUnitIds' => $module->learningUnits
                ->filter(fn ($learningUnit): bool => $progressService->isLearningUnitComplete($student, $learningUnit))
                ->pluck('id')
                ->all(),
            'moduleProgressPercentage' => $progressService->moduleCompletionPercentage($student, $module),
            'unlockedUnitIds' => $module->learningUnits
                ->filter(fn ($learningUnit): bool => $progressService->isLearningUnitUnlocked($student, $learningUnit))
                ->pluck('id')
                ->all(),
        ]);
    }
}
