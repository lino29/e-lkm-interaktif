<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Services\Learning\ActivityTemplateService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageActivities extends Component
{
    use WithFileUploads;

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

    public mixed $mediaFile = null;

    public string $mediaType = 'image';

    public ?string $mediaTitle = null;

    public ?string $mediaCaption = null;

    public ?string $mediaUrl = null;

    public ?string $mediaPath = null;

    public array $preview = [];

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
            $this->fillMediaFieldsFromDisplayConfig($template['display_config'] ?? []);

            session()->flash('status', 'Template berhasil diterapkan pada form.');
        }
    }

    public function updatedMediaFile(): void
    {
        $this->validateOnly('mediaFile', $this->mediaUploadValidationRules());

        if (! $this->mediaFile) {
            return;
        }

        $mimeType = $this->mediaFile->getMimeType();
        $this->mediaType = str_starts_with((string) $mimeType, 'video/') ? 'video' : 'image';
        $this->mediaTitle ??= pathinfo($this->mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
    }

    public function save(ActivityTemplateService $templateService): void
    {
        $unitIds = $this->teacherUnitIds();
        $validated = $this->validate([
            'learning_unit_id' => ['required', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'phase' => ['required', Rule::in(['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'])],
            'prompt' => ['nullable', 'string'],
            'input_type' => ['required', Rule::in(['short_text', 'essay', 'table', 'fields', 'file', 'discussion', 'project_form'])],
            'is_required' => ['boolean'],
            'order' => ['required', 'integer', 'min:1'],
            'answer_schema' => ['nullable', 'string'],
            'display_config' => ['nullable', 'string'],
            'validation_rules' => ['nullable', 'string'],
            'requires_teacher_review' => ['boolean'],
            'mediaType' => ['required', Rule::in(['image', 'video', 'youtube'])],
            'mediaTitle' => ['nullable', 'string', 'max:255'],
            'mediaCaption' => ['nullable', 'string', 'max:500'],
            'mediaUrl' => ['nullable', 'url', 'max:255'],
            'mediaFile' => $this->mediaUploadValidationRules()['mediaFile'],
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
        unset($validated['mediaType'], $validated['mediaTitle'], $validated['mediaCaption'], $validated['mediaUrl'], $validated['mediaFile']);

        $wasEditing = filled($this->editingActivityId);
        $activity = $wasEditing
            ? Activity::whereIn('learning_unit_id', $unitIds)->findOrFail($this->editingActivityId)
            : new Activity;

        $validated['display_config'] = $this->displayConfigForSave($validated['display_config'] ?? [], $activity);
        $validated['media_path'] = $validated['display_config']['media_path'] ?? null;

        $activity->fill($validated)->save();

        $savedWithMediaUpload = (bool) $this->mediaFile;
        $this->resetForm();
        session()->flash('status', $savedWithMediaUpload ? 'Aktivitas dan media pengamatan berhasil disimpan.' : ($wasEditing ? 'Aktivitas berhasil diperbarui.' : 'Aktivitas berhasil dibuat.'));
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
        $this->fillMediaFieldsFromDisplayConfig($activity->display_config ?? [], $activity->media_path);
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
        $this->reset(['editingActivityId', 'learning_unit_id', 'title', 'prompt', 'answer_schema', 'display_config', 'validation_rules', 'mediaFile', 'mediaTitle', 'mediaCaption', 'mediaUrl', 'mediaPath']);
        $this->phase = 'ayo_mengamati';
        $this->input_type = 'essay';
        $this->is_required = true;
        $this->requires_teacher_review = false;
        $this->order = 1;
        $this->mediaType = 'image';
    }

    /**
     * @return array<int, int>
     */
    private function teacherUnitIds(): array
    {
        return LearningUnit::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }

    /**
     * @return array{mediaFile: list<string>}
     */
    private function mediaUploadValidationRules(): array
    {
        return [
            'mediaFile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,mov', 'max:51200'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $displayConfig
     * @return array<string, mixed>|null
     */
    private function displayConfigForSave(?array $displayConfig, Activity $activity): ?array
    {
        $displayConfig ??= [];

        if ($this->phase !== 'ayo_mengamati') {
            return $displayConfig ?: null;
        }

        $mediaPath = $this->mediaPath;

        if ($this->mediaFile) {
            $mediaPath = $this->mediaFile->store('activity-media', 'public');
            $this->deleteStoredActivityMedia($activity->media_path);
        }

        if ($this->mediaType === 'youtube') {
            unset($displayConfig['media_path']);
            $displayConfig['media_type'] = 'youtube';
            $displayConfig['media_url'] = $this->mediaUrl;
        } elseif ($mediaPath) {
            unset($displayConfig['media_url']);
            $displayConfig['media_type'] = $this->mediaType;
            $displayConfig['media_path'] = $mediaPath;
        }

        if (filled($this->mediaTitle)) {
            $displayConfig['media_title'] = $this->mediaTitle;
        }

        if (filled($this->mediaCaption)) {
            $displayConfig['caption'] = $this->mediaCaption;
        }

        return $displayConfig ?: null;
    }

    /**
     * @param  array<string, mixed>  $displayConfig
     */
    private function fillMediaFieldsFromDisplayConfig(array $displayConfig, ?string $fallbackMediaPath = null): void
    {
        $this->mediaFile = null;
        $this->mediaType = $displayConfig['media_type'] ?? (($displayConfig['media_path'] ?? $fallbackMediaPath) ? 'image' : 'image');
        $this->mediaTitle = $displayConfig['media_title'] ?? null;
        $this->mediaCaption = $displayConfig['caption'] ?? null;
        $this->mediaUrl = $displayConfig['media_url'] ?? null;
        $this->mediaPath = $displayConfig['media_path'] ?? $fallbackMediaPath;
    }

    private function deleteStoredActivityMedia(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'activity-media/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
