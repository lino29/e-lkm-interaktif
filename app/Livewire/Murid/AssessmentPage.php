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

    public function mount(string|int $assessment): void
    {
        $this->currentAssessment = Assessment::with('questions.keywords', 'questions.rubrics', 'module')
            ->where('is_published', true)
            ->whereHas('module', fn ($query) => $query->where('status', 'published'))
            ->findOrFail($assessment);
        $this->latestAttempt = AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->latest()
            ->first();
    }

    public function submit(AssessmentScoringService $scoring): void
    {
        $attemptNumber = AssessmentAttempt::where('assessment_id', $this->currentAssessment->id)
            ->where('student_id', auth()->id())
            ->count() + 1;

        if ($attemptNumber > $this->currentAssessment->max_attempts) {
            session()->flash('status', 'Batas percobaan asesmen sudah tercapai.');

            return;
        }

        $attempt = AssessmentAttempt::create([
            'assessment_id' => $this->currentAssessment->id,
            'student_id' => auth()->id(),
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
        ]);

        $totalScore = 0.0;
        $maxScore = 0.0;

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
}
