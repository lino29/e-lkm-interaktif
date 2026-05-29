<?php

namespace App\Livewire\Murid;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use Livewire\Component;

class ActivityPage extends Component
{
    public Activity $activity;

    public string $answer_text = '';

    public string $answer_json_text = '';

    public function mount(string|int $activity): void
    {
        $this->activity = Activity::with('learningUnit.module')->findOrFail($activity);
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'answer_text' => ['nullable', 'string'],
            'answer_json_text' => ['nullable', 'string'],
        ]);

        ActivityAnswer::updateOrCreate(
            [
                'activity_id' => $this->activity->id,
                'user_id' => auth()->id(),
            ],
            [
                'answer_text' => $validated['answer_text'],
                'answer_json' => $this->decodeJson($validated['answer_json_text']),
                'submitted_at' => now(),
            ],
        );

        session()->flash('status', 'Jawaban aktivitas berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.murid.activity-page');
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
