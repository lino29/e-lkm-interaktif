<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageQuestions extends Component
{
    public ?int $assessment_id = null;

    public string $question_text = '';

    public string $question_type = 'multiple_choice';

    public string $options_json = '{}';

    public string $correct_answer_json = '[]';

    public ?string $reference_answer = null;

    public float $weight = 10;

    public string $keywords = '';

    public function save(): void
    {
        $assessmentIds = $this->teacherAssessmentIds();
        $validated = $this->validate([
            'assessment_id' => ['required', Rule::in($assessmentIds)],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice', 'complex_multiple_choice', 'true_false', 'matching', 'short_answer', 'essay'])],
            'reference_answer' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'keywords' => ['nullable', 'string'],
        ]);

        $question = Question::create([
            ...$validated,
            'options' => $this->decodeJson($this->options_json, []),
            'correct_answer' => $this->decodeJson($this->correct_answer_json, []),
            'order' => Question::where('assessment_id', $validated['assessment_id'])->max('order') + 1,
        ]);

        foreach (array_filter(array_map('trim', explode(',', $this->keywords))) as $keyword) {
            QuestionKeyword::create([
                'question_id' => $question->id,
                'keyword' => $keyword,
                'weight' => 1,
            ]);
        }

        $this->reset(['assessment_id', 'question_text', 'reference_answer', 'keywords']);
        $this->question_type = 'multiple_choice';
        $this->options_json = '{}';
        $this->correct_answer_json = '[]';
        $this->weight = 10;
        session()->flash('status', 'Soal berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.guru.manage-questions', [
            'assessments' => Assessment::whereIn('id', $this->teacherAssessmentIds())->orderBy('title')->get(),
            'questions' => Question::with('assessment', 'keywords')->whereIn('assessment_id', $this->teacherAssessmentIds())->latest()->get(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function teacherAssessmentIds(): array
    {
        return Assessment::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }

    private function decodeJson(string $json, array $fallback): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : $fallback;
    }
}
