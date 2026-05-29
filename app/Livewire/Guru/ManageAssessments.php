<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageAssessments extends Component
{
    public ?int $module_id = null;

    public ?int $learning_unit_id = null;

    public string $title = '';

    public string $type = 'formative';

    public int $kktp = 75;

    public int $max_attempts = 2;

    public bool $is_published = false;

    public function save(): void
    {
        $moduleIds = Module::where('created_by', auth()->id())->pluck('id')->all();
        $validated = $this->validate([
            'module_id' => ['required', Rule::in($moduleIds)],
            'learning_unit_id' => ['nullable', Rule::exists('learning_units', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['formative', 'final'])],
            'kktp' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'is_published' => ['boolean'],
        ]);

        Assessment::create($validated);
        $this->reset(['module_id', 'learning_unit_id', 'title', 'is_published']);
        $this->type = 'formative';
        $this->kktp = 75;
        $this->max_attempts = 2;
        session()->flash('status', 'Asesmen berhasil dibuat.');
    }

    public function render()
    {
        $modules = Module::where('created_by', auth()->id())->orderBy('title')->get();

        return view('livewire.guru.manage-assessments', [
            'modules' => $modules,
            'learningUnits' => LearningUnit::whereIn('module_id', $modules->pluck('id'))->orderBy('title')->get(),
            'assessments' => Assessment::with('module', 'learningUnit')->whereIn('module_id', $modules->pluck('id'))->latest()->get(),
        ]);
    }
}
