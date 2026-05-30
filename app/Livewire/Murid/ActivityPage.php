<?php

namespace App\Livewire\Murid;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Discussion;
use App\Services\Learning\ProgressService;
use Illuminate\Support\Facades\Storage;
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

    public function saveDraft(): void
    {
        $this->storeAnswer('draft');
        session()->flash('status', 'Draft berhasil disimpan.');
    }

    public function submit(): void
    {
        try {
            $this->storeAnswer('submitted');

            if ($this->currentActivity->phase === 'forum_diskusi' || $this->currentActivity->input_type === 'discussion') {
                Discussion::create([
                    'learning_unit_id' => $this->currentActivity->learning_unit_id,
                    'user_id' => auth()->id(),
                    'title' => $this->currentActivity->title,
                    'body' => $this->answer_text ?: 'Mengirim refleksi diskusi.',
                    'type' => 'reflection',
                ]);
            }

            app(ProgressService::class)->refreshLearningUnitProgress(auth()->user(), $this->currentActivity->learningUnit);

            session()->flash('status', 'Jawaban berhasil disubmit.');
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }

    private function storeAnswer(string $status): void
    {
        if ($this->existingAnswer?->status === 'reviewed') {
            return; // cannot edit reviewed answer
        }

        $validated = $this->validate([
            'answer_text' => ['nullable', 'string'],
            'table_data' => ['nullable', 'array'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $filePath = $this->existingAnswer?->file_path;

        if ($this->file) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $this->file->store('activity-answers', 'public');
            $this->reset(['file']);
        }

        $this->existingAnswer = ActivityAnswer::updateOrCreate(
            [
                'activity_id' => $this->currentActivity->id,
                'user_id' => auth()->id(),
            ],
            [
                'answer_text' => $validated['answer_text'],
                'answer_json' => $this->currentActivity->input_type === 'table' ? $validated['table_data'] : null,
                'file_path' => $filePath,
                'status' => $status,
                'submitted_at' => $status === 'submitted' ? now() : null,
            ],
        );
    }

    public function render()
    {
        return view('livewire.murid.activity-page', [
            'activity' => $this->currentActivity,
            'answer' => $this->existingAnswer,
        ]);
    }
}
