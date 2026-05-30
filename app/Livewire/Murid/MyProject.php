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
            abort(403);
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
        session()->flash('status', $status === 'draft' ? 'Draft proyek berhasil disimpan.' : 'Proyek berhasil dikirim.');
    }

    public function render()
    {
        return view('livewire.murid.my-project', [
            'modules' => Module::where('status', 'published')->orderBy('title')->get(),
            'projects' => Project::with('module')->where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}
