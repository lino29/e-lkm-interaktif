<?php

namespace App\Livewire\Murid;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Services\Learning\ActivityAnswerService;
use App\Services\Learning\ActivityDiscussionService;
use App\Services\Learning\ActivitySchemaValidator;
use App\Services\Learning\ProgressService;
use App\Services\Learning\ProjectDraftService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ActivityPage extends Component
{
    use WithFileUploads;

    public Activity $currentActivity;

    public ?ActivityAnswer $existingAnswer = null;

    public string $answer_text = '';

    public array $answer_json = [];

    public array $field_data = [];

    public array $table_data = [];

    public mixed $file = null;

    public function mount(string|int $activity): void
    {
        $this->currentActivity = Activity::with('learningUnit.module')
            ->whereHas('learningUnit.module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($activity);

        abort_unless(app(ProgressService::class)->isLearningUnitUnlocked(auth()->user(), $this->currentActivity->learningUnit), 403);

        $this->existingAnswer = ActivityAnswer::where('activity_id', $this->currentActivity->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($this->existingAnswer) {
            $this->answer_text = $this->existingAnswer->answer_text ?? '';
            $this->answer_json = $this->existingAnswer->answer_json ?? [];
            $this->table_data = $this->answer_json;
            $this->field_data = $this->answer_json[0] ?? $this->answer_json;

            return;
        }

        $this->initializeSchemaAnswer();
    }

    public function addTableRow(): void
    {
        $this->answer_json[] = $this->emptyTableRow();
        $this->table_data = $this->answer_json;
    }

    public function removeTableRow(int $index): void
    {
        if (! data_get($this->currentActivity->answer_schema, 'allow_delete', true)) {
            return;
        }

        $minRows = (int) data_get($this->currentActivity->answer_schema, 'min_rows', 1);

        if (count($this->answer_json) <= $minRows) {
            return;
        }

        unset($this->answer_json[$index]);
        $this->answer_json = array_values($this->answer_json);
        $this->table_data = $this->answer_json;
    }

    public function calculateComputedValue(?string $formula, array $row): mixed
    {
        return match ($formula) {
            'suhu_akhir - suhu_awal' => ((float) ($row['suhu_akhir'] ?? 0)) - ((float) ($row['suhu_awal'] ?? 0)),
            default => null,
        };
    }

    public function submit(): void
    {
        $this->processSubmit('submitted');
    }

    public function saveDraft(): void
    {
        $this->processSubmit('draft');
    }

    private function initializeSchemaAnswer(): void
    {
        $schema = $this->currentActivity->answer_schema ?? [];

        if ($this->currentActivity->input_type === 'table') {
            if (isset($schema['preset_rows']) && is_array($schema['preset_rows'])) {
                $this->answer_json = collect($schema['preset_rows'])
                    ->map(fn (array $row) => array_merge($this->emptyTableRow(), $row))
                    ->values()
                    ->all();
                $this->table_data = $this->answer_json;

                return;
            }

            $rows = (int) ($schema['min_rows'] ?? 1);
            $this->answer_json = collect(range(1, $rows))
                ->map(fn () => $this->emptyTableRow())
                ->all();
            $this->table_data = $this->answer_json;

            return;
        }

        if (isset($schema['fields']) && is_array($schema['fields'])) {
            $this->field_data = collect($schema['fields'])
                ->mapWithKeys(fn (array $field) => [$field['name'] => $field['value'] ?? null])
                ->toArray();
        }
    }

    private function emptyTableRow(): array
    {
        return collect(data_get($this->currentActivity->answer_schema, 'columns', []))
            ->mapWithKeys(fn (array $column) => [$column['name'] => $column['value'] ?? null])
            ->toArray();
    }

    private function processSubmit(string $status): void
    {
        $this->validateBaseInput();

        $schemaValidation = app(ActivitySchemaValidator::class)
            ->validate($this->currentActivity, $this->answer_json, $this->answer_text);

        if (! $schemaValidation['valid']) {
            $this->addError('activity', implode("\n", $schemaValidation['errors']));

            return;
        }

        $answer = app(ActivityAnswerService::class)->save(
            activity: $this->currentActivity,
            user: auth()->user(),
            answerText: $this->answer_text,
            answerJson: $this->answer_json,
            file: $this->file,
            status: $status
        );

        $this->existingAnswer = $answer;

        if ($this->currentActivity->phase === 'forum_diskusi' || $this->currentActivity->input_type === 'discussion') {
            app(ActivityDiscussionService::class)->sync($answer);
        }

        if ($this->currentActivity->input_type === 'project_form') {
            app(ProjectDraftService::class)->syncFromActivityAnswer($answer);
        }

        app(ProgressService::class)->refreshLearningUnitProgress(auth()->user(), $this->currentActivity->learningUnit);

        session()->flash('status', $status === 'submitted' ? 'Jawaban berhasil disubmit.' : 'Draft berhasil disimpan.');
    }

    private function validateBaseInput(): void
    {
        $this->validate([
            'answer_text' => ['nullable', 'string'],
            'answer_json' => ['nullable', 'array'],
            'table_data' => ['nullable', 'array'],
            'field_data' => ['nullable', 'array'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        if (in_array($this->currentActivity->input_type, ['project_form', 'fields'])) {
            $this->answer_json = [$this->field_data];
        }

        if ($this->currentActivity->input_type === 'table') {
            if (! $this->hasFilledTableValues($this->answer_json) && $this->hasFilledTableValues($this->table_data)) {
                $this->answer_json = $this->table_data;
            }

            $this->persistComputedFields();
            $this->table_data = $this->answer_json;
        }
    }

    private function hasFilledTableValues(array $rows): bool
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $value) {
                if ($value !== null && $value !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    private function persistComputedFields(): void
    {
        $columns = data_get($this->currentActivity->answer_schema, 'columns', []);

        foreach ($this->answer_json as $rowIndex => $row) {
            foreach ($columns as $column) {
                if (($column['type'] ?? null) === 'computed') {
                    $this->answer_json[$rowIndex][$column['name']] = $this->calculateComputedValue($column['formula'] ?? null, $row);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.murid.activity-page', [
            'activity' => $this->currentActivity,
            'answer' => $this->existingAnswer,
        ]);
    }
}
