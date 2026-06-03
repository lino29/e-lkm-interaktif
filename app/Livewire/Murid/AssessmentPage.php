<?php

namespace App\Livewire\Murid;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Services\Assessment\AssessmentScoringService;
use App\Services\Assessment\QuestionGroupService;
use App\Services\Learning\ProgressService;
use Illuminate\Support\Collection;
use Livewire\Component;

class AssessmentPage extends Component
{
    public Assessment $currentAssessment;

    /**
     * @var array<int, mixed>
     */
    public array $answers = [];

    public ?AssessmentAttempt $latestAttempt = null;

    public ?AssessmentAttempt $currentAttempt = null;

    public int $currentGroupIndex = 0;

    /**
     * @var array<string, bool>
     */
    public array $savedQuestionGroups = [];

    public function mount(string|int $assessment): void
    {
        $this->currentAssessment = Assessment::with('questions.keywords', 'questions.rubrics', 'module', 'learningUnit')
            ->where('is_published', true)
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($assessment);

        if ($this->currentAssessment->learningUnit) {
            $progressService = app(ProgressService::class);

            abort_unless($progressService->isLearningUnitUnlocked(auth()->user(), $this->currentAssessment->learningUnit), 403);
            abort_unless($progressService->isAssessmentUnlocked(auth()->user(), $this->currentAssessment), 403);
        }

        $this->latestAttempt = AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->first();

        $this->currentAttempt = AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->whereNull('submitted_at')
            ->where('status', 'sedang_dikerjakan')
            ->latest()
            ->first();

        if ($this->currentAttempt === null && $this->canStartNewAttempt()) {
            $this->currentAttempt = AssessmentAttempt::create([
                'assessment_id' => $this->currentAssessment->id,
                'student_id' => auth()->id(),
                'attempt_number' => $this->nextAttemptNumber(),
                'started_at' => now(),
            ]);
        }

        $this->loadDraftAnswers();
        $this->initializeEmptyAnswers();
    }

    private function initializeEmptyAnswers(): void
    {
        foreach ($this->currentAssessment->questions as $question) {
            if (! isset($this->answers[$question->id])) {
                if (in_array($question->question_type, ['complex_multiple_choice', 'matching'])) {
                    $this->answers[$question->id] = [];
                } else {
                    $this->answers[$question->id] = '';
                }
            } else {
                if (in_array($question->question_type, ['complex_multiple_choice', 'matching']) && is_string($this->answers[$question->id])) {
                    $decoded = json_decode($this->answers[$question->id], true);
                    $this->answers[$question->id] = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $this->answers[$question->id])));
                }
            }
        }
    }

    public function saveCurrentGroup(): void
    {
        $group = $this->persistCurrentGroupAnswers();

        if ($group === null) {
            return;
        }

        session()->flash('status', "Jawaban {$group['label']} berhasil disimpan.");
    }

    public function previousGroup(): void
    {
        if ($this->currentGroupIndex > 0) {
            $this->currentGroupIndex--;
        }
    }

    public function nextGroup(): void
    {
        $group = $this->persistCurrentGroupAnswers();

        if ($group === null) {
            return;
        }

        $lastIndex = max(0, $this->questionGroups()->count() - 1);
        if ($this->currentGroupIndex < $lastIndex) {
            $this->currentGroupIndex++;
        }

        session()->flash('status', "Jawaban {$group['label']} berhasil disimpan.");
    }

    public function submit(AssessmentScoringService $scoring)
    {
        $this->persistCurrentGroupAnswers(false);

        $attempt = $this->currentAttempt?->fresh();

        if ($attempt === null) {
            session()->flash('status', 'Batas percobaan asesmen sudah tercapai.');

            return;
        }

        if ($attempt->submitted_at !== null || $attempt->status !== 'sedang_dikerjakan') {
            session()->flash('status', 'Attempt asesmen ini sudah dikirim.');

            return;
        }

        // Validasi soal kosong
        $unansweredCount = 0;
        foreach ($this->currentAssessment->questions as $question) {
            $answer = $this->normalizeAnswer($question->id, $question->question_type);

            // Check if empty, allowing '0' or 0 as valid answers
            if (empty($answer) && $answer !== '0' && $answer !== 0) {
                $unansweredCount++;
            }
        }

        if ($unansweredCount > 0) {
            session()->flash('error', "Ada {$unansweredCount} soal yang belum diisi. Silakan cek kembali jawaban Anda pada seluruh bagian asesmen.");

            return;
        }

        $totalScore = 0.0;
        $maxScore = 0.0;

        $attempt->studentAnswers()->delete();

        foreach ($this->currentAssessment->questions as $question) {
            $answer = $this->normalizeAnswer($question->id, $question->question_type);
            $result = $scoring->scoreQuestion($question, $answer);
            $totalScore += $result['score'];
            $maxScore += $result['max_score'];

            StudentAnswer::create([
                'assessment_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'student_id' => auth()->id(),
                'answer_text' => is_string($answer) ? $answer : null,
                'answer_json' => is_array($answer) ? $answer : null,
                'score' => $result['score'],
                'rubric_score' => $result['rubric_score'] ?? null,
                'keyword_score' => $result['keyword_score'] ?? null,
                'similarity_score' => $result['similarity_score'] ?? null,
                'feedback' => $result['feedback'],
            ]);
        }

        $status = $scoring->determineStatus($totalScore, $maxScore, $this->currentAssessment->kktp);
        $attempt->update([
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'status' => $status,
            'submitted_at' => now(),
            'feedback' => $status === 'tuntas' ? 'Nilai sudah mencapai KKTP.' : 'Nilai belum mencapai KKTP, lakukan remedial.',
        ]);

        $progressService = app(ProgressService::class);
        $progressService->recordAssessment(auth()->user(), $this->currentAssessment, $totalScore, $status);

        if ($this->currentAssessment->learningUnit) {
            $progressService->refreshLearningUnitProgress(auth()->user(), $this->currentAssessment->learningUnit);
        }

        session()->flash('status', 'Asesmen berhasil disubmit dan dinilai otomatis.');

        return redirect()->route('murid.assessments.result', $this->currentAssessment->id);
    }

    public function render()
    {
        $questionGroups = $this->questionGroups();
        $lastGroupIndex = max(0, $questionGroups->count() - 1);
        $this->currentGroupIndex = min($this->currentGroupIndex, $lastGroupIndex);

        return view('livewire.murid.assessment-page', [
            'assessment' => $this->currentAssessment,
            'questionGroups' => $questionGroups,
            'currentGroup' => $questionGroups->values()->get($this->currentGroupIndex),
            'lastGroupIndex' => $lastGroupIndex,
            'isAttemptOpen' => $this->isAttemptOpen(),
        ]);
    }

    private function normalizeAnswer(int $questionId, string $type): mixed
    {
        $answer = $this->answers[$questionId] ?? '';

        if (in_array($type, ['complex_multiple_choice', 'matching'], true)) {
            if (is_array($answer)) {
                return array_filter($answer, fn ($value) => $value !== null && $value !== '');
            }

            $decoded = json_decode((string) $answer, true);

            return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', (string) $answer)));
        }

        return $answer;
    }

    /**
     * @return Collection<int, array{key: string, label: string, questions: Collection<int, Question>}>
     */
    private function questionGroups(): Collection
    {
        $questionGroupService = app(QuestionGroupService::class);
        $groupedQuestions = $this->currentAssessment->questions
            ->sortBy('order')
            ->groupBy(fn ($question) => $question->question_group ?? $questionGroupService->groupForType($question->question_type));

        $orderedGroups = collect(QuestionGroupService::GROUP_LABELS)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'questions' => $groupedQuestions->get($key, collect())->values(),
            ])
            ->filter(fn (array $group) => $group['questions']->isNotEmpty())
            ->values();

        $knownGroupKeys = array_keys(QuestionGroupService::GROUP_LABELS);
        $otherGroups = $groupedQuestions
            ->reject(fn (Collection $questions, string $key) => in_array($key, $knownGroupKeys, true))
            ->map(fn (Collection $questions, string $key) => [
                'key' => $key,
                'label' => $questionGroupService->labelForGroup($key),
                'questions' => $questions->values(),
            ])
            ->values();

        return $orderedGroups->concat($otherGroups)->values();
    }

    /**
     * @return array{key: string, label: string, questions: Collection<int, Question>}|null
     */
    private function currentQuestionGroup(): ?array
    {
        return $this->questionGroups()->values()->get($this->currentGroupIndex);
    }

    /**
     * @return array{key: string, label: string, questions: Collection<int, Question>}|null
     */
    private function persistCurrentGroupAnswers(bool $flashWhenClosed = true): ?array
    {
        $group = $this->currentQuestionGroup();

        if ($group === null) {
            return null;
        }

        if (! $this->isAttemptOpen()) {
            if ($flashWhenClosed) {
                session()->flash('status', 'Attempt asesmen ini sudah tidak dapat diubah.');
            }

            return null;
        }

        $attempt = $this->currentAttempt?->fresh();

        if ($attempt === null) {
            return null;
        }

        foreach ($group['questions'] as $question) {
            $answer = $this->normalizeAnswer($question->id, $question->question_type);

            StudentAnswer::updateOrCreate(
                [
                    'assessment_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'student_id' => auth()->id(),
                    'answer_text' => is_string($answer) ? $answer : null,
                    'answer_json' => is_array($answer) ? $answer : null,
                    'score' => 0,
                    'rubric_score' => null,
                    'keyword_score' => null,
                    'similarity_score' => null,
                    'feedback' => null,
                ],
            );
        }

        $this->savedQuestionGroups[$group['key']] = true;

        return $group;
    }

    private function isAttemptOpen(): bool
    {
        return $this->currentAttempt !== null
            && $this->currentAttempt->submitted_at === null
            && $this->currentAttempt->status === 'sedang_dikerjakan';
    }

    private function loadDraftAnswers(): void
    {
        if ($this->currentAttempt === null || ! $this->isAttemptOpen()) {
            return;
        }

        $draftAnswers = $this->currentAttempt->studentAnswers()->get();

        foreach ($draftAnswers as $draftAnswer) {
            $this->answers[$draftAnswer->question_id] = $draftAnswer->answer_json ?? $draftAnswer->answer_text ?? '';
        }

        $questionGroupService = app(QuestionGroupService::class);
        $questionGroupsById = $this->currentAssessment->questions
            ->keyBy('id')
            ->map(fn ($question) => $question->question_group ?? $questionGroupService->groupForType($question->question_type));

        foreach ($draftAnswers as $draftAnswer) {
            $groupKey = $questionGroupsById->get($draftAnswer->question_id);

            if ($groupKey !== null) {
                $this->savedQuestionGroups[$groupKey] = true;
            }
        }
    }

    private function canStartNewAttempt(): bool
    {
        if ($this->latestAttempt?->status === 'tuntas') {
            return false;
        }

        return $this->submittedAndActiveAttemptCount() < $this->currentAssessment->max_attempts;
    }

    private function nextAttemptNumber(): int
    {
        return $this->submittedAndActiveAttemptCount() + 1;
    }

    private function submittedAndActiveAttemptCount(): int
    {
        return AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->count();
    }
}
