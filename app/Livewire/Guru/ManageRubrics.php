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

        Rubric::create($validated);
        $this->reset(['question_id', 'criterion', 'level', 'description', 'score']);
        session()->flash('status', 'Rubrik berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.guru.manage-rubrics', [
            'questions' => Question::whereIn('id', $this->teacherQuestionIds())->orderBy('question_text')->get(),
            'rubrics' => Rubric::with('question')->whereIn('question_id', $this->teacherQuestionIds())->latest()->get(),
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
}
