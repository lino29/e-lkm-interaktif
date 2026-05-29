<?php

namespace App\Services\Nlp;

use App\Models\Question;
use App\Models\QuestionKeyword;

class KeywordMatcher
{
    public function __construct(private readonly TextPreprocessor $preprocessor) {}

    /**
     * @return array{score: float, matched: array<int, string>, total_weight: float}
     */
    public function score(Question $question, string $answer): array
    {
        $keywords = $question->keywords;

        if ($keywords->isEmpty()) {
            return ['score' => 0.0, 'matched' => [], 'total_weight' => 0.0];
        }

        $normalizedAnswer = $this->preprocessor->normalize($answer);
        $totalWeight = (float) $keywords->sum('weight');
        $matchedWeight = 0.0;
        $matched = [];

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeKeyword($keyword);

            if ($normalizedKeyword !== '' && str_contains($normalizedAnswer, $normalizedKeyword)) {
                $matchedWeight += (float) $keyword->weight;
                $matched[] = $keyword->keyword;
            }
        }

        return [
            'score' => $totalWeight > 0 ? round(($matchedWeight / $totalWeight) * 100, 2) : 0.0,
            'matched' => $matched,
            'total_weight' => $totalWeight,
        ];
    }

    private function normalizeKeyword(QuestionKeyword $keyword): string
    {
        if (filled($keyword->normalized_keyword)) {
            return $this->preprocessor->normalize($keyword->normalized_keyword);
        }

        return $this->preprocessor->normalize($keyword->keyword);
    }
}
