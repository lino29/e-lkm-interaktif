<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageMaterials extends Component
{
    public ?int $learning_unit_id = null;

    public string $title = '';

    public ?string $content = null;

    public string $material_type = 'text';

    public int $order = 1;

    public function save(): void
    {
        $unitIds = $this->teacherUnitIds();
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'material_type' => ['required', 'string', 'max:50'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        Material::create($validated);
        $this->reset(['learning_unit_id', 'title', 'content']);
        $this->material_type = 'text';
        $this->order = 1;
        session()->flash('status', 'Materi berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.guru.manage-materials', [
            'learningUnits' => LearningUnit::whereIn('id', $this->teacherUnitIds())->orderBy('title')->get(),
            'materials' => Material::with('learningUnit.module')->whereIn('learning_unit_id', $this->teacherUnitIds())->latest()->get(),
        ]);
    }

    private function teacherUnitIds(): array
    {
        return LearningUnit::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }
}
