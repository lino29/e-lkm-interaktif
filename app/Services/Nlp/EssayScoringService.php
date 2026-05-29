<?php

namespace App\Services\Nlp;

use App\Models\Question;

class EssayScoringService
{
    public function __construct(
        private readonly KeywordMatcher $keywordMatcher,
        private readonly SimilarityService $similarityService,
    ) {}

    /**
     * @return array{score: float, rubric_score: float, keyword_score: float, similarity_score: float, feedback: string}
     */
    public function score(Question $question, string $answer, ?float $rubricScore = null): array
    {
        $rubricScore ??= $this->defaultRubricScore($question);
        $keywordResult = $this->keywordMatcher->score($question, $answer);
        $keywordScore = $keywordResult['score'];
        $similarityScore = $this->similarityService->score($answer, $question->reference_answer);
        $score = round(($rubricScore * 0.4) + ($keywordScore * 0.3) + ($similarityScore * 0.3), 2);

        return [
            'score' => $score,
            'rubric_score' => round($rubricScore, 2),
            'keyword_score' => $keywordScore,
            'similarity_score' => $similarityScore,
            'feedback' => $this->feedback($score, $keywordResult['matched']),
        ];
    }

    private function defaultRubricScore(Question $question): float
    {
        if ($question->rubrics->isEmpty()) {
            return 0.0;
        }

        return min(100.0, (float) $question->rubrics->avg('score'));
    }

    /**
     * @param  array<int, string>  $matchedKeywords
     */
    private function feedback(float $score, array $matchedKeywords): string
    {
        if ($score >= 80) {
            return 'Jawaban sudah kuat dan sesuai dengan rubrik utama.';
        }

        if ($matchedKeywords !== []) {
            return 'Jawaban sudah memuat beberapa kata kunci, tetapi penjelasan perlu diperdalam.';
        }

        return 'Jawaban perlu dilengkapi dengan konsep dan kata kunci utama.';
    }
}
