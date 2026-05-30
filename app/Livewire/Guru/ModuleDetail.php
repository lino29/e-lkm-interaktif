<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ModuleDetail extends Component
{
    public Module $currentModule;

    public function mount(string|int $module): void
    {
        $this->currentModule = Module::where('created_by', auth()->id())->findOrFail($module);
    }

    public function toggleStatus(): void
    {
        $this->currentModule->update([
            'status' => $this->currentModule->status === 'published' ? 'draft' : 'published',
        ]);

        $this->currentModule->refresh();
    }

    public function deleteLearningUnit(int $learningUnitId): void
    {
        LearningUnit::where('module_id', $this->currentModule->id)->findOrFail($learningUnitId)->delete();
        $this->currentModule->refresh();
    }

    public function deleteMaterial(int $materialId): void
    {
        $material = Material::whereHas('learningUnit', fn ($query) => $query->where('module_id', $this->currentModule->id))
            ->findOrFail($materialId);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        $this->currentModule->refresh();
    }

    public function deleteActivity(int $activityId): void
    {
        Activity::whereHas('learningUnit', fn ($query) => $query->where('module_id', $this->currentModule->id))
            ->findOrFail($activityId)
            ->delete();

        $this->currentModule->refresh();
    }

    public function render()
    {
        return view('livewire.guru.module-detail', [
            'module' => $this->currentModule->load([
                'subject',
                'learningUnits.materials',
                'learningUnits.activities.answers',
                'learningUnits.assessments.questions',
                'assessments.questions',
                'glossaries',
                'references',
            ]),
        ]);
    }
}
