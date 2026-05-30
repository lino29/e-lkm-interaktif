<?php

namespace App\Livewire\Guru;

use App\Models\Module;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageModules extends Component
{
    use WithFileUploads;

    public ?int $editingModuleId = null;

    public ?int $subject_id = null;

    public string $title = '';

    public ?string $introduction = null;

    public ?string $learning_objectives = null;

    public string $status = 'draft';

    public int $kktp = 75;

    public int $max_attempts = 2;

    public mixed $cover = null;

    public ?string $existingCoverPath = null;

    public function save(): void
    {
        $validated = $this->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'introduction' => ['nullable', 'string'],
            'learning_objectives' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'kktp' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        $wasEditing = filled($this->editingModuleId);
        $module = $wasEditing
            ? Module::where('created_by', auth()->id())->findOrFail($this->editingModuleId)
            : new Module(['created_by' => auth()->id()]);

        $coverPath = $module->cover_path;

        if ($this->cover) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            $coverPath = $this->cover->store('module-covers', 'public');
        }

        $module->fill([
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'slug' => $this->editingModuleId ? $module->slug : $this->uniqueSlug($validated['title']),
            'cover_path' => $coverPath,
            'introduction' => $validated['introduction'],
            'learning_objectives' => $validated['learning_objectives'],
            'status' => $validated['status'],
            'kktp' => $validated['kktp'],
            'max_attempts' => $validated['max_attempts'],
        ])->save();

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Modul berhasil diperbarui.' : 'Modul berhasil dibuat.');
    }

    public function edit(int $moduleId): void
    {
        $module = Module::where('created_by', auth()->id())->findOrFail($moduleId);

        $this->editingModuleId = $module->id;
        $this->subject_id = $module->subject_id;
        $this->title = $module->title;
        $this->introduction = $module->introduction;
        $this->learning_objectives = $module->learning_objectives;
        $this->status = $module->status;
        $this->kktp = $module->kktp;
        $this->max_attempts = $module->max_attempts;
        $this->existingCoverPath = $module->cover_path;
        $this->cover = null;
    }

    public function toggleStatus(int $moduleId): void
    {
        $module = Module::where('created_by', auth()->id())->findOrFail($moduleId);

        $module->update([
            'status' => $module->status === 'published' ? 'draft' : 'published',
        ]);
    }

    public function delete(int $moduleId): void
    {
        $module = Module::where('created_by', auth()->id())->findOrFail($moduleId);

        if ($module->cover_path) {
            Storage::disk('public')->delete($module->cover_path);
        }

        $module->delete();
        $this->resetForm();
        session()->flash('status', 'Modul berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.guru.manage-modules', [
            'subjects' => Subject::orderBy('name')->get(),
            'modules' => Module::with('subject')
                ->withCount(['learningUnits', 'assessments'])
                ->where('created_by', auth()->id())
                ->latest()
                ->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingModuleId', 'subject_id', 'title', 'introduction', 'learning_objectives', 'cover', 'existingCoverPath']);
        $this->status = 'draft';
        $this->kktp = 75;
        $this->max_attempts = 2;
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (Module::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
