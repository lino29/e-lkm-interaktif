<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\Module;
use App\Models\Question;
use App\Models\Rubric;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageRubrics extends Component
{
    public ?int $editingRubricId = null;

    public ?int $question_id = null;

    public string $criterion = '';

    public ?string $level = null;

    public ?string $description = null;

    public float $score = 0;

    public function save(): void
    {
        $validated = $this->validate([
            'question_id' => ['required', Rule::in($this->teacherQuestionIds())],
            'criterion' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $rubric = $this->editingRubricId
            ? Rubric::whereIn('question_id', $this->teacherQuestionIds())->findOrFail($this->editingRubricId)
            : new Rubric;

        $rubric->fill($validated)->save();
        $wasEditing = filled($this->editingRubricId);

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Rubrik berhasil diperbarui.' : 'Rubrik berhasil dibuat.');
    }

    public function edit(int $rubricId): void
    {
        $rubric = Rubric::whereIn('question_id', $this->teacherQuestionIds())->findOrFail($rubricId);

        $this->editingRubricId = $rubric->id;
        $this->question_id = $rubric->question_id;
        $this->criterion = $rubric->criterion;
        $this->level = $rubric->level;
        $this->description = $rubric->description;
        $this->score = (float) $rubric->score;
    }

    public function delete(int $rubricId): void
    {
        Rubric::whereIn('question_id', $this->teacherQuestionIds())->findOrFail($rubricId)->delete();
        $this->resetForm();

        session()->flash('status', 'Rubrik berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.guru.manage-rubrics', [
            'questions' => Question::with('assessment')->whereIn('id', $this->teacherQuestionIds())->orderBy('question_text')->get(),
            'rubrics' => Rubric::with('question.assessment')->whereIn('question_id', $this->teacherQuestionIds())->latest()->get(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function teacherQuestionIds(): array
    {
        $assessmentIds = Assessment::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id');

        return Question::whereIn('assessment_id', $assessmentIds)->pluck('id')->all();
    }

    private function resetForm(): void
    {
        $this->reset(['editingRubricId', 'question_id', 'criterion', 'level', 'description']);
        $this->score = 0;
    }
}
