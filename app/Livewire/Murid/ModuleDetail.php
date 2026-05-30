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

        $completedUnitIds = $module->learningUnits
            ->filter(fn ($learningUnit): bool => $progressService->isLearningUnitComplete($student, $learningUnit))
            ->pluck('id')
            ->all();

        return view('livewire.murid.module-detail', [
            'module' => $module,
            'completedUnitIds' => $completedUnitIds,
            'moduleProgressPercentage' => $progressService->moduleCompletionPercentage($student, $module),
            'unlockedUnitIds' => $module->learningUnits
                ->filter(fn ($learningUnit): bool => $progressService->isLearningUnitUnlocked($student, $learningUnit))
                ->pluck('id')
                ->all(),
            'allUnitsCompleted' => count($completedUnitIds) === $module->learningUnits->count() && $module->learningUnits->count() > 0,
        ]);
    }
}
