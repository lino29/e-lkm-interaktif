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
            $this->table_data = $this->existingAnswer->answer_json ?? [];
        } else {
            // Initialize table rows if it's a table
            if ($this->currentActivity->input_type === 'table') {
                $minRows = data_get($this->currentActivity->answer_schema, 'min_rows', 1);
                $columns = data_get($this->currentActivity->answer_schema, 'columns', []);
                for ($i = 0; $i < $minRows; $i++) {
                    $row = [];
                    foreach ($columns as $col) {
                        $row[$col['name']] = '';
                    }
                    $this->table_data[] = $row;
                }
            }
        }
    }

    public function addTableRow(): void
    {
        $columns = data_get($this->currentActivity->answer_schema, 'columns', []);
        $row = [];
        foreach ($columns as $col) {
            $row[$col['name']] = '';
        }
        $this->table_data[] = $row;
    }

    public function removeTableRow(int $index): void
    {
        $minRows = data_get($this->currentActivity->answer_schema, 'min_rows', 1);
        if (count($this->table_data) > $minRows) {
            unset($this->table_data[$index]);
            $this->table_data = array_values($this->table_data);
        }
    }

    public function submit(): void
    {
        $this->processSubmit('submitted');
    }

    public function saveDraft(): void
    {
        $this->processSubmit('draft');
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

        try {
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

            $msg = $status === 'submitted' ? 'Jawaban berhasil disubmit.' : 'Draft berhasil disimpan.';
            session()->flash('status', $msg);
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }

    private function validateBaseInput(): void
    {
        $this->validate([
            'answer_text' => ['nullable', 'string'],
            'table_data' => ['nullable', 'array'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        // table_data holds the raw data bound to frontend, map to answer_json for the service
        $this->answer_json = $this->table_data;
    }

    public function render()
    {
        return view('livewire.murid.activity-page', [
            'activity' => $this->currentActivity,
            'answer' => $this->existingAnswer,
        ]);
    }
}
