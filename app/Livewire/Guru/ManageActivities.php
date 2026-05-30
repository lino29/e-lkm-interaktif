<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Services\Learning\ActivityTemplateService;
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

    public ?string $answer_schema = null;

    public ?string $display_config = null;

    public ?string $validation_rules = null;

    public bool $requires_teacher_review = false;

    public function applyTemplate(): void
    {
        $learningUnitOrder = null;
        if ($this->learning_unit_id) {
            $learningUnitOrder = LearningUnit::find($this->learning_unit_id)?->order;
        }

        $templateService = app(ActivityTemplateService::class);
        $template = $templateService->templateFor($this->phase, $learningUnitOrder);

        if ($template) {
            $this->title = $template['title'] ?? $this->title;
            $this->prompt = $template['prompt'] ?? $this->prompt;
            $this->input_type = $template['input_type'] ?? $this->input_type;
            $this->requires_teacher_review = $template['requires_teacher_review'] ?? false;
            $this->answer_schema = $template['answer_schema'] ? json_encode($template['answer_schema'], JSON_PRETTY_PRINT) : null;
            $this->display_config = $template['display_config'] ? json_encode($template['display_config'], JSON_PRETTY_PRINT) : null;
            $this->validation_rules = $template['validation_rules'] ? json_encode($template['validation_rules'], JSON_PRETTY_PRINT) : null;

            session()->flash('status', 'Template berhasil diterapkan pada form.');
        }
    }

    public function save(ActivityTemplateService $templateService): void
    {
        $unitIds = $this->teacherUnitIds();
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'phase' => ['required', Rule::in(['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'])],
            'prompt' => ['nullable', 'string'],
            'input_type' => ['required', Rule::in(['short_text', 'essay', 'table', 'file', 'discussion', 'project_form'])],
            'is_required' => ['boolean'],
            'order' => ['required', 'integer', 'min:1'],
            'answer_schema' => ['nullable', 'string'],
            'display_config' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'string'],
            'requires_teacher_review' => ['boolean'],
        ]);

        if (! $templateService->isValidSchema($this->answer_schema)) {
            $this->addError('answer_schema', 'Format JSON pada Answer Schema tidak valid.');

            return;
        }
        if (! $templateService->isValidSchema($this->display_config)) {
            $this->addError('display_config', 'Format JSON pada Display Config tidak valid.');

            return;
        }
        if (! $templateService->isValidSchema($this->validation_rules)) {
            $this->addError('validation_rules', 'Format JSON pada Validation Rules tidak valid.');

            return;
        }

        $validated['answer_schema'] = $this->answer_schema ? json_decode($this->answer_schema, true) : null;
        $validated['display_config'] = $this->display_config ? json_decode($this->display_config, true) : null;
        $validated['validation_rules'] = $this->validation_rules ? json_decode($this->validation_rules, true) : null;

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
        $this->answer_schema = $activity->answer_schema ? json_encode($activity->answer_schema, JSON_PRETTY_PRINT) : null;
        $this->display_config = $activity->display_config ? json_encode($activity->display_config, JSON_PRETTY_PRINT) : null;
        $this->validation_rules = $activity->validation_rules ? json_encode($activity->validation_rules, JSON_PRETTY_PRINT) : null;
        $this->requires_teacher_review = $activity->requires_teacher_review;
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
        $this->reset(['editingActivityId', 'learning_unit_id', 'title', 'prompt', 'answer_schema', 'display_config', 'validation_rules']);
        $this->phase = 'ayo_mengamati';
        $this->input_type = 'essay';
        $this->is_required = true;
        $this->requires_teacher_review = false;
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
