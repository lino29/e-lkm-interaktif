<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageMaterials extends Component
{
    use WithFileUploads;

    public ?int $editingMaterialId = null;

    public ?int $learning_unit_id = null;

    public string $title = '';

    public ?string $content = null;

    public string $material_type = 'text';

    public mixed $file = null;

    public ?string $existingFilePath = null;

    public int $order = 1;

    public function save(): void
    {
        $unitIds = $this->teacherUnitIds();
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'material_type' => ['required', Rule::in(['text', 'image', 'video', 'simulation', 'file', 'link'])],
            'file' => ['nullable', 'file', 'max:10240'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $wasEditing = filled($this->editingMaterialId);
        $material = $wasEditing
            ? Material::whereIn('learning_unit_id', $unitIds)->findOrFail($this->editingMaterialId)
            : new Material;

        $filePath = $material->file_path;

        if ($this->file) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            $filePath = $this->file->store('materials', 'public');
        }

        $material->fill([
            'learning_unit_id' => $validated['learning_unit_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'material_type' => $validated['material_type'],
            'file_path' => $filePath,
            'order' => $validated['order'],
        ])->save();

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Materi berhasil diperbarui.' : 'Materi berhasil dibuat.');
    }

    public function edit(int $materialId): void
    {
        $material = Material::whereIn('learning_unit_id', $this->teacherUnitIds())->findOrFail($materialId);

        $this->editingMaterialId = $material->id;
        $this->learning_unit_id = $material->learning_unit_id;
        $this->title = $material->title;
        $this->content = $material->content;
        $this->material_type = $material->material_type;
        $this->existingFilePath = $material->file_path;
        $this->order = $material->order;
        $this->file = null;
    }

    public function delete(int $materialId): void
    {
        $material = Material::whereIn('learning_unit_id', $this->teacherUnitIds())->findOrFail($materialId);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
        $this->resetForm();
        session()->flash('status', 'Materi berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.guru.manage-materials', [
            'learningUnits' => LearningUnit::whereIn('id', $this->teacherUnitIds())->orderBy('title')->get(),
            'materials' => Material::with('learningUnit.module')->whereIn('learning_unit_id', $this->teacherUnitIds())->orderBy('learning_unit_id')->orderBy('order')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingMaterialId', 'learning_unit_id', 'title', 'content', 'file', 'existingFilePath']);
        $this->material_type = 'text';
        $this->order = 1;
    }

    /**
     * @return array<int, int>
     */
    private function teacherUnitIds(): array
    {
        return LearningUnit::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }
}
