<?php

namespace App\Livewire\Guru;

use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageDiscussions extends Component
{
    public ?int $module_id = null;

    public ?int $learning_unit_id = null;

    public array $replyBodies = [];

    public function togglePinned(int $discussionId): void
    {
        $discussion = $this->teacherDiscussionQuery()->findOrFail($discussionId);

        $discussion->update(['is_pinned' => ! $discussion->is_pinned]);
    }

    public function delete(int $discussionId): void
    {
        // Also delete replies if it's a parent, handled by DB cascade
        $discussion = Discussion::whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $this->teacherModuleIds()))
            ->findOrFail($discussionId);
        $discussion->delete();
    }

    public function replyToDiscussion(int $discussionId): void
    {
        $body = $this->replyBodies[$discussionId] ?? '';
        $this->validate([
            "replyBodies.{$discussionId}" => ['required', 'string', 'min:3'],
        ]);

        $parent = $this->teacherDiscussionQuery()->findOrFail($discussionId);

        Discussion::create([
            'learning_unit_id' => $parent->learning_unit_id,
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'title' => 'Balasan guru',
            'body' => $body,
            'type' => 'reply',
        ]);

        unset($this->replyBodies[$discussionId]);
        session()->flash('status', 'Balasan berhasil dikirim.');
    }

    public function render()
    {
        $moduleIds = $this->teacherModuleIds();
        $learningUnits = LearningUnit::whereIn('module_id', $moduleIds)->orderBy('title')->get();

        $this->validateOnlyFilters($moduleIds, $learningUnits->pluck('id')->all());

        return view('livewire.guru.manage-discussions', [
            'modules' => Module::whereIn('id', $moduleIds)->orderBy('title')->get(),
            'learningUnits' => $learningUnits,
            'discussions' => $this->teacherDiscussionQuery()
                ->when($this->module_id, fn ($query) => $query->whereHas('learningUnit', fn ($unitQuery) => $unitQuery->where('module_id', $this->module_id)))
                ->when($this->learning_unit_id, fn ($query) => $query->where('learning_unit_id', $this->learning_unit_id))
                ->withCount('replies')
                ->latest()
                ->get(),
        ]);
    }

    private function teacherDiscussionQuery()
    {
        return Discussion::with(['learningUnit.module', 'user', 'replies.user'])
            ->whereNull('parent_id')
            ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $this->teacherModuleIds()));
    }

    /**
     * @return array<int, int>
     */
    private function teacherModuleIds(): array
    {
        return Module::where('created_by', auth()->id())->pluck('id')->all();
    }

    /**
     * @param  array<int, int>  $moduleIds
     * @param  array<int, int>  $learningUnitIds
     */
    private function validateOnlyFilters(array $moduleIds, array $learningUnitIds): void
    {
        $this->validate([
            'module_id' => ['nullable', Rule::in($moduleIds)],
            'learning_unit_id' => ['nullable', Rule::in($learningUnitIds)],
        ]);
    }
}
