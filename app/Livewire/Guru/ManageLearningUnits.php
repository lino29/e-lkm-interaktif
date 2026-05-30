<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageLearningUnits extends Component
{
    public ?int $editingLearningUnitId = null;

    public ?int $module_id = null;

    public string $title = '';

    public ?string $description = null;

    public ?string $objectives = null;

    public int $order = 1;

    public function save(): void
    {
        $moduleIds = $this->teacherModuleIds();
        $validated = $this->validate([
            'module_id' => ['required', Rule::in($moduleIds)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $wasEditing = filled($this->editingLearningUnitId);
        $learningUnit = $wasEditing
            ? LearningUnit::whereIn('module_id', $moduleIds)->findOrFail($this->editingLearningUnitId)
            : new LearningUnit;

        $learningUnit->fill([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title'], (int) $validated['module_id'], $learningUnit->id),
        ])->save();

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Kegiatan belajar berhasil diperbarui.' : 'Kegiatan belajar berhasil dibuat.');
    }

    public function edit(int $learningUnitId): void
    {
        $learningUnit = LearningUnit::whereIn('module_id', $this->teacherModuleIds())->findOrFail($learningUnitId);

        $this->editingLearningUnitId = $learningUnit->id;
        $this->module_id = $learningUnit->module_id;
        $this->title = $learningUnit->title;
        $this->description = $learningUnit->description;
        $this->objectives = $learningUnit->objectives;
        $this->order = $learningUnit->order;
    }

    public function delete(int $learningUnitId): void
    {
        LearningUnit::whereIn('module_id', $this->teacherModuleIds())->findOrFail($learningUnitId)->delete();
        $this->resetForm();
        session()->flash('status', 'Kegiatan belajar berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $modules = Module::where('created_by', auth()->id())->orderBy('title')->get();

        return view('livewire.guru.manage-learning-units', [
            'modules' => $modules,
            'learningUnits' => LearningUnit::with('module')
                ->withCount(['materials', 'activities', 'assessments'])
                ->whereIn('module_id', $modules->pluck('id'))
                ->orderBy('module_id')
                ->orderBy('order')
                ->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingLearningUnitId', 'module_id', 'title', 'description', 'objectives']);
        $this->order = 1;
    }

    /**
     * @return array<int, int>
     */
    private function teacherModuleIds(): array
    {
        return Module::where('created_by', auth()->id())->pluck('id')->all();
    }

    private function uniqueSlug(string $title, int $moduleId, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            LearningUnit::where('module_id', $moduleId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
