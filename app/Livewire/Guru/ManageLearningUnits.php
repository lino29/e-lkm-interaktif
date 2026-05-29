<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageLearningUnits extends Component
{
    public ?int $module_id = null;

    public string $title = '';

    public ?string $objectives = null;

    public int $order = 1;

    public function save(): void
    {
        $moduleIds = Module::where('created_by', auth()->id())->pluck('id')->all();
        $validated = $this->validate([
            'module_id' => ['required', Rule::in($moduleIds)],
            'title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        LearningUnit::create([
            ...$validated,
            'slug' => Str::slug($validated['title']),
        ]);

        $this->reset(['module_id', 'title', 'objectives']);
        $this->order = 1;
        session()->flash('status', 'Kegiatan belajar berhasil dibuat.');
    }

    public function render()
    {
        $modules = Module::where('created_by', auth()->id())->orderBy('title')->get();

        return view('livewire.guru.manage-learning-units', [
            'modules' => $modules,
            'learningUnits' => LearningUnit::with('module')
                ->whereIn('module_id', $modules->pluck('id'))
                ->orderBy('module_id')
                ->orderBy('order')
                ->get(),
        ]);
    }
}
