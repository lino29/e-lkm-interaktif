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

    public string $answer_text = '';

    public string $answer_json_text = '';

    public mixed $file = null;

    public function mount(string|int $activity): void
    {
        $this->currentActivity = Activity::with('learningUnit.module')
            ->whereHas('learningUnit.module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($activity);

        abort_unless(app(ProgressService::class)->isLearningUnitUnlocked(auth()->user(), $this->currentActivity->learningUnit), 403);
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'answer_text' => ['nullable', 'string'],
            'answer_json_text' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $existingAnswer = ActivityAnswer::where('activity_id', $this->currentActivity->id)
            ->where('user_id', auth()->id())
            ->first();
        $filePath = $existingAnswer?->file_path;

        if ($this->file) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            $filePath = $this->file->store('activity-answers', 'public');
        }

        ActivityAnswer::updateOrCreate(
            [
                'activity_id' => $this->currentActivity->id,
                'user_id' => auth()->id(),
            ],
            [
                'answer_text' => $validated['answer_text'],
                'answer_json' => $this->decodeJson($validated['answer_json_text']),
                'file_path' => $filePath,
                'submitted_at' => now(),
            ],
        );

        if ($this->currentActivity->phase === 'forum_diskusi' || $this->currentActivity->input_type === 'discussion') {
            Discussion::create([
                'learning_unit_id' => $this->currentActivity->learning_unit_id,
                'user_id' => auth()->id(),
                'title' => $this->currentActivity->title,
                'body' => $validated['answer_text'] ?: 'Mengirim refleksi diskusi.',
                'type' => 'reflection',
            ]);
        }

        app(ProgressService::class)->refreshLearningUnitProgress(auth()->user(), $this->currentActivity->learningUnit);

        $this->reset(['file']);
        session()->flash('status', 'Jawaban aktivitas berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.murid.activity-page', [
            'activity' => $this->currentActivity,
        ]);
    }

    private function decodeJson(?string $json): ?array
    {
        if (blank($json)) {
            return null;
        }

        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
