<?php

namespace App\Livewire\Murid;

use App\Models\Module;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class MyProject extends Component
{
    use WithFileUploads;

    public ?int $module_id = null;

    public string $project_title = '';

    public ?string $problem = null;

    public ?string $objective = null;

    public ?string $tools_materials = null;

    public ?string $procedure = null;

    public ?string $collected_data = null;

    public ?string $expected_result = null;

    public ?string $conclusion = null;

    public mixed $file = null;

    public ?string $existing_file_path = null;

    public function updatedModuleId($value)
    {
        $this->resetForm();
        if ($value) {
            $project = Project::where('module_id', $value)->where('user_id', auth()->id())->first();
            if ($project) {
                $this->project_title = $project->project_title;
                $this->problem = $project->problem;
                $this->objective = $project->objective;
                $this->tools_materials = $project->tools_materials;
                $this->procedure = $project->procedure;
                $this->collected_data = $project->collected_data;
                $this->expected_result = $project->expected_result;
                $this->conclusion = $project->conclusion;
                $this->existing_file_path = $project->file_path;
            }
        }
    }

    public function resetForm()
    {
        $this->project_title = '';
        $this->problem = null;
        $this->objective = null;
        $this->tools_materials = null;
        $this->procedure = null;
        $this->collected_data = null;
        $this->expected_result = null;
        $this->conclusion = null;
        $this->file = null;
        $this->existing_file_path = null;
    }

    public function save(string $status = 'submitted'): void
    {
        $status = in_array($status, ['draft', 'submitted'], true) ? $status : 'submitted';

        $validated = $this->validate([
            'module_id' => ['required', Rule::exists('modules', 'id')->where('status', 'published')],
            'project_title' => ['required', 'string', 'max:255'],
            'problem' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'tools_materials' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'collected_data' => ['nullable', 'string'],
            'expected_result' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $project = Project::where('module_id', $validated['module_id'])
            ->where('user_id', auth()->id())
            ->first();

        if ($project && auth()->user()->cannot('update', $project)) {
            abort(403, 'Proyek sudah direview atau anda tidak memiliki akses.');
        }

        $filePath = $project?->file_path;

        if ($this->file) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $this->file->store('projects', 'public');
        }

        Project::updateOrCreate(
            [
                'module_id' => $validated['module_id'],
                'user_id' => auth()->id(),
            ],
            [
                ...collect($validated)->except('file')->all(),
                'user_id' => auth()->id(),
                'file_path' => $filePath,
                'status' => $status,
            ],
        );

        $this->reset(['file']);
        $this->updatedModuleId($this->module_id);

        session()->flash('status', $status === 'draft' ? 'Draft proyek berhasil disimpan.' : 'Proyek berhasil dikirim.');
    }

    public function downloadExistingFile()
    {
        if ($this->existing_file_path && Storage::disk('public')->exists($this->existing_file_path)) {
            return Storage::disk('public')->download($this->existing_file_path);
        }
    }

    public function render()
    {
        $currentProject = null;
        if ($this->module_id) {
            $currentProject = Project::where('module_id', $this->module_id)->where('user_id', auth()->id())->first();
        }

        return view('livewire.murid.my-project', [
            'modules' => Module::where('status', 'published')->orderBy('title')->get(),
            'projects' => Project::with('module')->where('user_id', auth()->id())->latest()->get(),
            'currentProject' => $currentProject,
        ]);
    }
}
