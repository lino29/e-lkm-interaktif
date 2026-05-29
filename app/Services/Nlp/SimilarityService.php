<?php

namespace App\Services\Nlp;

class SimilarityService
{
    public function __construct(private readonly TextPreprocessor $preprocessor) {}

    public function score(string $answer, ?string $referenceAnswer): float
    {
        if (blank($answer) || blank($referenceAnswer)) {
            return 0.0;
        }

        $answerTokens = array_unique($this->preprocessor->tokens($answer));
        $referenceTokens = array_unique($this->preprocessor->tokens((string) $referenceAnswer));

        if ($answerTokens === [] || $referenceTokens === []) {
            return 0.0;
        }

        $intersection = array_intersect($answerTokens, $referenceTokens);
        $union = array_unique([...$answerTokens, ...$referenceTokens]);

        return round((count($intersection) / count($union)) * 100, 2);
    }
}
