<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageActivities extends Component
{
    public ?int $editingActivityId = null;

    public ?int $learning_unit_id = null;

    public string $title = '';

    public string $phase = 'ayo_mengamati';

    public ?string $prompt = null;

    public string $input_type = 'essay';

    public bool $is_required = true;

    public int $order = 1;

    public function save(): void
    {
        $unitIds = $this->teacherUnitIds();
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'phase' => ['required', Rule::in(['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'])],
            'prompt' => ['nullable', 'string'],
            'input_type' => ['required', Rule::in(['short_text', 'essay', 'table', 'file', 'discussion'])],
            'is_required' => ['boolean'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $wasEditing = filled($this->editingActivityId);
        $activity = $wasEditing
            ? Activity::whereIn('learning_unit_id', $unitIds)->findOrFail($this->editingActivityId)
            : new Activity;

        $activity->fill($validated)->save();

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Aktivitas berhasil diperbarui.' : 'Aktivitas berhasil dibuat.');
    }

    public function edit(int $activityId): void
    {
        $activity = Activity::whereIn('learning_unit_id', $this->teacherUnitIds())->findOrFail($activityId);

        $this->editingActivityId = $activity->id;
        $this->learning_unit_id = $activity->learning_unit_id;
        $this->title = $activity->title;
        $this->phase = $activity->phase;
        $this->prompt = $activity->prompt;
        $this->input_type = $activity->input_type;
        $this->is_required = $activity->is_required;
        $this->order = $activity->order;
    }

    public function delete(int $activityId): void
    {
        Activity::whereIn('learning_unit_id', $this->teacherUnitIds())->findOrFail($activityId)->delete();
        $this->resetForm();
        session()->flash('status', 'Aktivitas berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.guru.manage-activities', [
            'learningUnits' => LearningUnit::whereIn('id', $this->teacherUnitIds())->orderBy('title')->get(),
            'activities' => Activity::with('learningUnit.module')->whereIn('learning_unit_id', $this->teacherUnitIds())->orderBy('learning_unit_id')->orderBy('order')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingActivityId', 'learning_unit_id', 'title', 'prompt']);
        $this->phase = 'ayo_mengamati';
        $this->input_type = 'essay';
        $this->is_required = true;
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
