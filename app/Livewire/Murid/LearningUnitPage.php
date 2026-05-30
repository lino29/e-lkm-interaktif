<?php

namespace App\Livewire\Murid;

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
        $this->currentLearningUnit = LearningUnit::with('module', 'materials', 'media', 'activities', 'assessments')
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);
        app(ProgressService::class)->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);
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
            'discussions' => Discussion::with('user', 'replies.user')
                ->where('learning_unit_id', $this->currentLearningUnit->id)
                ->whereNull('parent_id')
                ->latest()
                ->get(),
        ]);
    }
}
