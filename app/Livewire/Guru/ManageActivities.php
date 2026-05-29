<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageActivities extends Component
{
    public ?int $learning_unit_id = null;

    public string $title = '';

    public string $phase = 'ayo_mengamati';

    public ?string $prompt = null;

    public string $input_type = 'essay';

    public int $order = 1;

    public function save(): void
    {
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($this->teacherUnitIds())],
            'title' => ['required', 'string', 'max:255'],
            'phase' => ['required', Rule::in(['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'])],
            'prompt' => ['nullable', 'string'],
            'input_type' => ['required', Rule::in(['short_text', 'essay', 'table', 'file', 'discussion'])],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        Activity::create($validated);
        $this->reset(['learning_unit_id', 'title', 'prompt']);
        $this->phase = 'ayo_mengamati';
        $this->input_type = 'essay';
        $this->order = 1;
        session()->flash('status', 'Aktivitas berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.guru.manage-activities', [
            'learningUnits' => LearningUnit::whereIn('id', $this->teacherUnitIds())->orderBy('title')->get(),
            'activities' => Activity::with('learningUnit.module')->whereIn('learning_unit_id', $this->teacherUnitIds())->latest()->get(),
        ]);
    }

    private function teacherUnitIds(): array
    {
        return LearningUnit::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }
}
