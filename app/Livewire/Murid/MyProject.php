<?php

namespace App\Livewire\Murid;

use App\Models\Module;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MyProject extends Component
{
    public ?int $module_id = null;

    public string $project_title = '';

    public ?string $problem = null;

    public ?string $objective = null;

    public ?string $procedure = null;

    public ?string $conclusion = null;

    public function save(): void
    {
        $validated = $this->validate([
            'module_id' => ['required', Rule::exists('modules', 'id')],
            'project_title' => ['required', 'string', 'max:255'],
            'problem' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
        ]);

        Project::updateOrCreate(
            [
                'module_id' => $validated['module_id'],
                'user_id' => auth()->id(),
            ],
            [
                ...$validated,
                'user_id' => auth()->id(),
                'status' => 'submitted',
            ],
        );

        session()->flash('status', 'Proyek berhasil dikirim.');
    }

    public function render()
    {
        return view('livewire.murid.my-project', [
            'modules' => Module::where('status', 'published')->orderBy('title')->get(),
            'projects' => Project::with('module')->where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}
