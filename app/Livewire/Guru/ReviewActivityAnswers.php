<?php

namespace App\Livewire\Guru;

use App\Models\ActivityAnswer;
use App\Models\Module;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewActivityAnswers extends Component
{
    use WithPagination;

    public $moduleId = '';

    public $phase = '';

    public $status = 'submitted';

    public function render()
    {
        $teacherModuleIds = Module::where('created_by', auth()->id())->pluck('id');

        $query = ActivityAnswer::with(['user', 'activity.learningUnit.module'])
            ->whereHas('activity.learningUnit', function ($q) use ($teacherModuleIds) {
                $q->whereIn('module_id', $teacherModuleIds);
            })
            ->where('status', '!=', 'draft');

        if ($this->moduleId) {
            $query->whereHas('activity.learningUnit', function ($q) {
                $q->where('module_id', $this->moduleId);
            });
        }

        if ($this->phase) {
            $query->whereHas('activity', function ($q) {
                $q->where('phase', $this->phase);
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return view('livewire.guru.review-activity-answers', [
            'answers' => $query->latest('submitted_at')->paginate(10),
            'modules' => Module::where('created_by', auth()->id())->get(),
        ]);
    }

    public function saveReview(int $answerId, $score, $feedback)
    {
        $teacherModuleIds = Module::where('created_by', auth()->id())->pluck('id');
        $answer = ActivityAnswer::whereHas('activity.learningUnit', function ($q) use ($teacherModuleIds) {
            $q->whereIn('module_id', $teacherModuleIds);
        })->findOrFail($answerId);

        $answer->update([
            'score' => $score !== '' ? $score : null,
            'teacher_feedback' => $feedback,
            'status' => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('status', 'Review berhasil disimpan.');
    }
}
