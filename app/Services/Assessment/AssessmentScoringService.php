<?php

namespace App\Services\Assessment;

use App\Models\Question;
use App\Services\Nlp\EssayScoringService;
use App\Services\Nlp\KeywordMatcher;
use Illuminate\Support\Arr;

class AssessmentScoringService
{
    public function __construct(
        private readonly KeywordMatcher $keywordMatcher,
        private readonly EssayScoringService $essayScoringService,
    ) {}

    /**
     * @return array{score: float, max_score: float, feedback: string, rubric_score?: float, keyword_score?: float, similarity_score?: float}
     */
    public function scoreQuestion(Question $question, mixed $answer): array
    {
        $maxScore = (float) $question->weight;
        $percentage = match ($question->question_type) {
            'multiple_choice' => $this->scoreSingleChoice($question, $answer),
            'complex_multiple_choice' => $this->scoreComplexChoice($question, $answer),
            'true_false' => $this->scoreTrueFalse($question, $answer),
            'matching' => $this->scoreMatching($question, $answer),
            'short_answer' => $this->scoreShortAnswer($question, (string) $answer),
            'essay' => null,
            default => 0.0,
        };

        if ($question->question_type === 'essay') {
            $essayResult = $this->essayScoringService->score($question, (string) $answer);
            $essayResult['max_score'] = $maxScore;
            $essayResult['score'] = round(($essayResult['score'] / 100) * $maxScore, 2);

            return $essayResult;
        }

        return [
            'score' => round(($percentage / 100) * $maxScore, 2),
            'max_score' => $maxScore,
            'feedback' => $percentage >= 100 ? 'Jawaban benar.' : 'Jawaban perlu diperbaiki.',
        ];
    }

    public function determineStatus(float $totalScore, float $maxScore, int $kktp): string
    {
        if ($maxScore <= 0) {
            return 'remedial';
        }

        $percentage = ($totalScore / $maxScore) * 100;

        return $percentage >= $kktp ? 'tuntas' : 'remedial';
    }

    private function scoreSingleChoice(Question $question, mixed $answer): float
    {
        $correct = Arr::first((array) $question->correct_answer);

        return (string) $answer === (string) $correct ? 100.0 : 0.0;
    }

    private function scoreComplexChoice(Question $question, mixed $answer): float
    {
        $correctAnswers = array_values(array_unique(array_map('strval', (array) $question->correct_answer)));
        $studentAnswers = array_values(array_unique(array_map('strval', (array) $answer)));

        if ($correctAnswers === []) {
            return 0.0;
        }

        $correctSelected = count(array_intersect($studentAnswers, $correctAnswers));
        $wrongSelected = count(array_diff($studentAnswers, $correctAnswers));
        $raw = ($correctSelected / count($correctAnswers)) * 100;
        $penalty = $wrongSelected * (100 / count($correctAnswers));

        return max(0.0, round($raw - $penalty, 2));
    }

    private function scoreTrueFalse(Question $question, mixed $answer): float
    {
        $correct = Arr::first((array) $question->correct_answer);

        return filter_var($answer, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === filter_var($correct, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
            ? 100.0
            : 0.0;
    }

    private function scoreMatching(Question $question, mixed $answer): float
    {
        $correctPairs = (array) $question->correct_answer;
        $studentPairs = (array) $answer;

        if ($correctPairs === []) {
            return 0.0;
        }

        $correctCount = 0;

        foreach ($correctPairs as $left => $right) {
            if (($studentPairs[$left] ?? null) === $right) {
                $correctCount++;
            }
        }

        return round(($correctCount / count($correctPairs)) * 100, 2);
    }

    private function scoreShortAnswer(Question $question, string $answer): float
    {
        return $this->keywordMatcher->score($question, $answer)['score'];
    }
}
