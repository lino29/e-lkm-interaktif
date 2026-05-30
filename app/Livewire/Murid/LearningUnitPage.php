<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Services\Learning\ProgressService;
use Livewire\Component;

class LearningUnitPage extends Component
{
    public LearningUnit $currentLearningUnit;

    public string $discussionBody = '';

    /**
     * @var array<int, string>
     */
    public array $replyBodies = [];

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::with(['module', 'materials', 'media', 'activities' => fn ($q) => $q->orderBy('order'), 'assessments'])
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);

        $progressService = app(ProgressService::class);

        abort_unless($progressService->isLearningUnitUnlocked(auth()->user(), $this->currentLearningUnit), 403);

        $progressService->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);
    }

    private function getActivityStatuses(): array
    {
        $answers = ActivityAnswer::where('user_id', auth()->id())
            ->whereIn('activity_id', $this->currentLearningUnit->activities->pluck('id'))
            ->get()
            ->keyBy('activity_id');

        $statuses = [];
        $isLocked = false;

        foreach ($this->currentLearningUnit->activities as $activity) {
            $answer = $answers->get($activity->id);
            $status = $answer ? $answer->status : 'belum_mulai';

            $statuses[$activity->id] = [
                'status' => $status,
                'is_locked' => $isLocked,
                'answer' => $answer,
            ];

            if ($activity->is_required && ! in_array($status, ['submitted', 'reviewed'])) {
                $isLocked = true; // Lock subsequent activities
            }
        }

        return $statuses;
    }

    public function submitDiscussion(): void
    {
        $validated = $this->validate([
            'discussionBody' => ['required', 'string', 'min:3'],
        ]);

        Discussion::create([
            'learning_unit_id' => $this->currentLearningUnit->id,
            'user_id' => auth()->id(),
            'title' => 'Diskusi '.$this->currentLearningUnit->title,
            'body' => $validated['discussionBody'],
            'type' => 'forum',
        ]);

        $this->reset('discussionBody');
        session()->flash('status', 'Komentar diskusi berhasil dikirim.');
    }

    public function replyToDiscussion(int $discussionId): void
    {
        $body = $this->replyBodies[$discussionId] ?? '';
        $this->validate([
            "replyBodies.{$discussionId}" => ['required', 'string', 'min:3'],
        ]);

        $parent = Discussion::where('learning_unit_id', $this->currentLearningUnit->id)
            ->whereNull('parent_id')
            ->findOrFail($discussionId);

        Discussion::create([
            'learning_unit_id' => $this->currentLearningUnit->id,
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'title' => 'Balasan diskusi',
            'body' => $body,
            'type' => 'reply',
        ]);

        unset($this->replyBodies[$discussionId]);
        session()->flash('status', 'Balasan diskusi berhasil dikirim.');
    }

    public function render()
    {
        return view('livewire.murid.learning-unit-page', [
            'learningUnit' => $this->currentLearningUnit,
            'activityStatuses' => $this->getActivityStatuses(),
            'discussions' => Discussion::with('user', 'replies.user')
                ->where('learning_unit_id', $this->currentLearningUnit->id)
                ->whereNull('parent_id')
                ->latest()
                ->get(),
        ]);
    }
}
