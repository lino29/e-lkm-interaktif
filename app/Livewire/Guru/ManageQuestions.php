<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Services\Assessment\QuestionGroupService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageQuestions extends Component
{
    public ?int $editingQuestionId = null;

    public ?int $assessment_id = null;

    public ?int $filter_assessment_id = null;

    public string $question_text = '';

    public string $question_type = 'multiple_choice';

    // Dynamic arrays instead of JSON strings
    public array $options = [];

    public array $correct_answers = [];

    // For matching questions
    public array $matching_left = [];

    public array $matching_right = [];

    public array $matching_answers = [];

    public ?string $reference_answer = null;

    public bool $use_ai_scoring = false;

    public float $weight = 10;

    public int $order = 1;

    public string $keywords = '';

    public function mount(): void
    {
        $this->initializeOptionsForType();
    }

    public function updatedQuestionType(): void
    {
        $this->initializeOptionsForType();
    }

    private function initializeOptionsForType(): void
    {
        $this->options = [];
        $this->correct_answers = [];
        $this->matching_left = [];
        $this->matching_right = [];
        $this->matching_answers = [];

        if (in_array($this->question_type, ['multiple_choice', 'complex_multiple_choice'])) {
            $this->options = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
        } elseif ($this->question_type === 'true_false') {
            $this->options = ['True' => 'Benar', 'False' => 'Salah'];
            $this->correct_answers = ['True'];
        } elseif ($this->question_type === 'matching') {
            $this->matching_left = ['1' => '', '2' => ''];
            $this->matching_right = ['A' => '', 'B' => '', 'C' => ''];
        }
    }

    public function addOption(): void
    {
        $nextChar = chr(65 + count($this->options));
        $this->options[$nextChar] = '';
    }

    public function removeOption(string $key): void
    {
        unset($this->options[$key]);
        if (in_array($key, $this->correct_answers)) {
            $this->correct_answers = array_values(array_diff($this->correct_answers, [$key]));
        }
    }

    public function addMatchingLeft(): void
    {
        $nextNum = (string) (count($this->matching_left) + 1);
        $this->matching_left[$nextNum] = '';
    }

    public function removeMatchingLeft(string $key): void
    {
        unset($this->matching_left[$key]);
        unset($this->matching_answers[$key]);
    }

    public function addMatchingRight(): void
    {
        $nextChar = chr(65 + count($this->matching_right));
        $this->matching_right[$nextChar] = '';
    }

    public function removeMatchingRight(string $key): void
    {
        unset($this->matching_right[$key]);
        foreach ($this->matching_answers as $k => $v) {
            if ($v === $key) {
                unset($this->matching_answers[$k]);
            }
        }
    }

    public function toggleCorrectAnswer(string $key): void
    {
        if ($this->question_type === 'multiple_choice' || $this->question_type === 'true_false') {
            $this->correct_answers = [$key];
        } else {
            if (in_array($key, $this->correct_answers)) {
                $this->correct_answers = array_values(array_diff($this->correct_answers, [$key]));
            } else {
                $this->correct_answers[] = $key;
            }
        }
    }

    public function save(): void
    {
        $assessmentIds = $this->teacherAssessmentIds();
        $validated = $this->validate([
            'assessment_id' => ['required', Rule::in($assessmentIds)],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice', 'complex_multiple_choice', 'true_false', 'matching', 'short_answer', 'essay'])],
            'reference_answer' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'order' => ['required', 'integer', 'min:1'],
            'keywords' => ['nullable', 'string'],
            'use_ai_scoring' => ['boolean'],
        ]);

        $finalOptions = [];
        $finalAnswers = [];

        if (in_array($this->question_type, ['multiple_choice', 'complex_multiple_choice', 'true_false'], true)) {
            $finalOptions = array_filter($this->options, fn ($val) => trim($val) !== '');
            $finalAnswers = $this->correct_answers;

            if (empty($finalAnswers)) {
                $this->addError('correct_answers', 'Kunci jawaban belum dipilih.');

                return;
            }
        } elseif ($this->question_type === 'matching') {
            $finalOptions = [
                'left' => array_filter($this->matching_left, fn ($val) => trim($val) !== ''),
                'right' => array_filter($this->matching_right, fn ($val) => trim($val) !== ''),
            ];
            $finalAnswers = $this->matching_answers;

            if (count($finalAnswers) !== count($finalOptions['left'])) {
                $this->addError('matching_answers', 'Pastikan semua pasangan dari kolom kiri memiliki jawaban.');

                return;
            }
        } elseif (in_array($this->question_type, ['short_answer', 'essay'], true)) {
            $finalOptions = ['use_ai_scoring' => $this->use_ai_scoring];
        }

        unset($validated['keywords'], $validated['use_ai_scoring']);

        $question = $this->editingQuestionId
            ? Question::whereIn('assessment_id', $assessmentIds)->findOrFail($this->editingQuestionId)
            : new Question;

        $question->fill([
            ...$validated,
            'question_group' => app(QuestionGroupService::class)->groupForType($validated['question_type']),
            'options' => $finalOptions,
            'correct_answer' => $finalAnswers,
        ])->save();

        $question->keywords()->delete();
        if (in_array($this->question_type, ['short_answer', 'essay'], true) && filled($this->keywords)) {
            foreach (array_filter(array_map('trim', explode(',', $this->keywords))) as $keyword) {
                QuestionKeyword::create([
                    'question_id' => $question->id,
                    'keyword' => $keyword,
                    'weight' => 1,
                ]);
            }
        }

        $wasEditing = filled($this->editingQuestionId);
        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Soal berhasil diperbarui.' : 'Soal berhasil dibuat.');
    }

    public function edit(int $questionId): void
    {
        $question = Question::with('keywords')->whereIn('assessment_id', $this->teacherAssessmentIds())->findOrFail($questionId);

        $this->editingQuestionId = $question->id;
        $this->assessment_id = $question->assessment_id;
        $this->question_text = $question->question_text;
        $this->question_type = $question->question_type;
        $this->reference_answer = $question->reference_answer;
        $this->weight = (float) $question->weight;
        $this->order = $question->order;
        $this->keywords = $question->keywords->pluck('keyword')->implode(', ');

        $opts = $question->options ?? [];
        $ans = $question->correct_answer ?? [];

        if ($this->question_type === 'matching') {
            $this->matching_left = $opts['left'] ?? [];
            $this->matching_right = $opts['right'] ?? [];
            $this->matching_answers = $ans;
        } elseif (in_array($this->question_type, ['short_answer', 'essay'], true)) {
            $this->use_ai_scoring = $opts['use_ai_scoring'] ?? false;
        } else {
            $this->options = $opts;
            $this->correct_answers = $ans;
        }
    }

    public function delete(int $questionId): void
    {
        Question::whereIn('assessment_id', $this->teacherAssessmentIds())->findOrFail($questionId)->delete();
        $this->resetForm();

        session()->flash('status', 'Soal berhasil dihapus.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function updatedFilterAssessmentId(): void
    {
        if (! $this->editingQuestionId && $this->filter_assessment_id) {
            $this->assessment_id = $this->filter_assessment_id;
        }
    }

    public function render()
    {
        $assessmentIds = $this->teacherAssessmentIds();

        return view('livewire.guru.manage-questions', [
            'assessments' => Assessment::whereIn('id', $assessmentIds)->with('module')->orderBy('title')->get(),
            'questions' => $this->filter_assessment_id
                ? Question::with('assessment.module', 'keywords')
                    ->where('assessment_id', $this->filter_assessment_id)
                    ->whereIn('assessment_id', $assessmentIds)
                    ->orderBy('order')
                    ->get()
                : collect(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function teacherAssessmentIds(): array
    {
        return Assessment::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'))->pluck('id')->all();
    }

    private function resetForm(): void
    {
        $this->reset(['editingQuestionId', 'assessment_id', 'question_text', 'reference_answer', 'keywords', 'use_ai_scoring']);
        $this->question_type = 'multiple_choice';
        $this->weight = 10;
        $this->order = 1;
        $this->initializeOptionsForType();
    }
}
