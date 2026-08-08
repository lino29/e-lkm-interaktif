<?php

namespace App\Livewire\Guru;

use App\Models\ActivityAnswer;
use App\Models\LearningUnitGrade;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewActivityAnswers extends Component
{
    use WithPagination;

    public string $moduleId = '';

    public string $status = 'pending';

    public string $search = '';

    public ?int $selectedGradeId = null;

    public string $gradeScore = '';

    public string $gradeFeedback = '';

    public function mount(): void
    {
        $gradeId = request()->integer('grade');

        if ($gradeId > 0) {
            $this->selectSubmission($gradeId);
        }
    }

    public function updatedModuleId(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function selectSubmission(int $gradeId): void
    {
        $grade = $this->teacherGradeQuery()->findOrFail($gradeId);

        $this->selectedGradeId = $grade->id;
        $this->gradeScore = $grade->score !== null ? (string) (float) $grade->score : '';
        $this->gradeFeedback = $grade->feedback ?? '';

        auth()->user()->unreadNotifications
            ->first(fn ($notification): bool => (int) data_get($notification->data, 'learning_unit_grade_id') === $grade->id)
            ?->markAsRead();

        $this->dispatch('kb-notification-read');
    }

    public function closeSubmission(): void
    {
        $this->reset(['selectedGradeId', 'gradeScore', 'gradeFeedback']);
    }

    public function saveGrade(): void
    {
        $validated = $this->validate([
            'selectedGradeId' => ['required', 'integer'],
            'gradeScore' => ['required', 'numeric', 'min:0', 'max:20'],
            'gradeFeedback' => ['nullable', 'string', 'max:2000'],
        ], [
            'gradeScore.max' => 'Nilai maksimal untuk setiap KB adalah 20.',
        ]);

        $grade = $this->teacherGradeQuery()->findOrFail($validated['selectedGradeId']);
        $grade->update([
            'score' => $validated['gradeScore'],
            'feedback' => $validated['gradeFeedback'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->gradeScore = (string) (float) $grade->fresh()->score;
        $this->dispatch('kb-notification-read');
        session()->flash('status', 'Nilai dan umpan balik KB berhasil disimpan.');
    }

    public function render()
    {
        $teacherModules = Module::query()
            ->where('created_by', auth()->id())
            ->orderBy('title')
            ->get(['id', 'title']);

        $grades = $this->teacherGradeQuery()
            ->with([
                'student:id,name,email',
                'learningUnit:id,module_id,title,order',
                'learningUnit.module:id,title',
                'reviewer:id,name',
            ])
            ->when($this->moduleId !== '', fn (Builder $query) => $query->whereHas(
                'learningUnit',
                fn (Builder $learningUnitQuery) => $learningUnitQuery->where('module_id', $this->moduleId),
            ))
            ->when($this->status === 'pending', fn (Builder $query) => $query->whereNull('score'))
            ->when($this->status === 'reviewed', fn (Builder $query) => $query->whereNotNull('score'))
            ->when($this->search !== '', fn (Builder $query) => $query->whereHas(
                'student',
                fn (Builder $studentQuery) => $studentQuery
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%'),
            ))
            ->latest()
            ->paginate(10);

        $selectedGrade = $this->selectedGrade();

        return view('livewire.guru.review-activity-answers', [
            'grades' => $grades,
            'modules' => $teacherModules,
            'selectedGrade' => $selectedGrade,
            'selectedAnswers' => $this->selectedAnswers($selectedGrade),
            'studentModuleGrades' => $this->studentModuleGrades($selectedGrade),
        ]);
    }

    private function teacherGradeQuery(): Builder
    {
        return LearningUnitGrade::query()
            ->whereHas('learningUnit.module', fn (Builder $query) => $query->where('created_by', auth()->id()));
    }

    private function selectedGrade(): ?LearningUnitGrade
    {
        if ($this->selectedGradeId === null) {
            return null;
        }

        return $this->teacherGradeQuery()
            ->with(['student:id,name,email', 'learningUnit.module:id,title', 'reviewer:id,name'])
            ->find($this->selectedGradeId);
    }

    /** @return Collection<int, ActivityAnswer> */
    private function selectedAnswers(?LearningUnitGrade $grade): Collection
    {
        if (! $grade) {
            return collect();
        }

        return ActivityAnswer::query()
            ->with('activity:id,learning_unit_id,title,phase,prompt,input_type,answer_schema,order')
            ->where('user_id', $grade->student_id)
            ->where('status', '!=', 'draft')
            ->whereHas('activity', fn (Builder $query) => $query->where('learning_unit_id', $grade->learning_unit_id))
            ->latest('submitted_at')
            ->get();
    }

    /** @return Collection<int, LearningUnitGrade> */
    private function studentModuleGrades(?LearningUnitGrade $grade): Collection
    {
        if (! $grade) {
            return collect();
        }

        return LearningUnitGrade::query()
            ->with('learningUnit:id,module_id,title,order')
            ->where('student_id', $grade->student_id)
            ->whereHas('learningUnit', fn (Builder $query) => $query
                ->where('module_id', $grade->learningUnit->module_id)
                ->whereBetween('order', [1, 5]))
            ->get()
            ->sortBy('learningUnit.order')
            ->values();
    }
}
