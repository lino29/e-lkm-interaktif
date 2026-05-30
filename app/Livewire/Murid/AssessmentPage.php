<?php

namespace App\Livewire\Murid;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\StudentAnswer;
use App\Services\Assessment\AssessmentScoringService;
use App\Services\Learning\ProgressService;
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

    public function mount(string|int $assessment): void
    {
        $this->currentAssessment = Assessment::with('questions.keywords', 'questions.rubrics', 'module')
            ->where('is_published', true)
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($assessment);

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
    }

    public function submit(AssessmentScoringService $scoring): void
    {
        $attempt = $this->currentAttempt?->fresh();

        if ($attempt === null) {
            session()->flash('status', 'Batas percobaan asesmen sudah tercapai.');

            return;
        }

        if ($attempt->submitted_at !== null || $attempt->status !== 'sedang_dikerjakan') {
            session()->flash('status', 'Attempt asesmen ini sudah dikirim.');

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

        app(ProgressService::class)->recordAssessment(auth()->user(), $this->currentAssessment, $totalScore, $status);
        $this->latestAttempt = $attempt->fresh();
        $this->currentAttempt = $this->latestAttempt;
        session()->flash('status', 'Asesmen berhasil dinilai otomatis.');
    }

    public function render()
    {
        return view('livewire.murid.assessment-page', [
            'assessment' => $this->currentAssessment,
        ]);
    }

    private function normalizeAnswer(int $questionId, string $type): mixed
    {
        $answer = $this->answers[$questionId] ?? '';

        if (in_array($type, ['complex_multiple_choice', 'matching'], true)) {
            $decoded = json_decode((string) $answer, true);

            return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', (string) $answer)));
        }

        return $answer;
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
