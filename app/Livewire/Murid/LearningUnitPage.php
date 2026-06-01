<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Services\Learning\LearningUnitOutlineService;
use App\Services\Learning\ProgressService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LearningUnitPage extends Component
{
    public LearningUnit $currentLearningUnit;

    public ?int $activeSectionId = null;

    public string $discussionBody = '';

    /**
     * @var array<int, string>
     */
    public array $replyBodies = [];

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::with([
            'module',
            'rootSections.children',
            'sections.children',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ])
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($learningUnit);

        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        $this->currentLearningUnit = $this->currentLearningUnit->fresh([
            'module',
            'rootSections.children',
            'sections.children',
            'materials.media',
            'media',
            'activities.answers',
            'assessments.questions',
        ]);

        $progressService = app(ProgressService::class);

        abort_unless($progressService->isLearningUnitUnlocked(auth()->user(), $this->currentLearningUnit), 403);

        $progressService->markStarted(auth()->user(), $this->currentLearningUnit->module, $this->currentLearningUnit);

        $this->activeSectionId = $this->currentLearningUnit->rootSections->firstWhere('section_type', 'activity_group')?->id
            ?? $this->currentLearningUnit->rootSections->first()?->id;
    }

    public function openSection(int $sectionId): void
    {
        $exists = $this->currentLearningUnit
            ->sections()
            ->whereKey($sectionId)
            ->exists();

        if ($exists) {
            $this->activeSectionId = $sectionId;
        }
    }

    #[Computed]
    public function activeSection(): ?LearningUnitSection
    {
        if (! $this->activeSectionId) {
            return $this->currentLearningUnit->rootSections->firstWhere('section_type', 'activity_group')
                ?? $this->currentLearningUnit->rootSections->first();
        }

        return $this->currentLearningUnit
            ->sections()
            ->with('children')
            ->find($this->activeSectionId);
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
                $isLocked = true;
            }
        }

        return $statuses;
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
