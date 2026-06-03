<?php

namespace App\Services\Nlp;

use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $score = 0.0;
        $feedback = '';

        if (! empty($question->options['use_ai_scoring']) && config('services.gemini.key')) {
            $aiResult = $this->scoreWithGemini($question->reference_answer, $answer);
            if ($aiResult !== null) {
                $score = $aiResult['score'];
                $feedback = $aiResult['feedback']."\n\n(Dinilai oleh AI)";
            }
        }

        if ($score === 0.0) {
            $score = round(($rubricScore * 0.4) + ($keywordScore * 0.3) + ($similarityScore * 0.3), 2);
            $feedback = $this->feedback($score, $keywordResult['matched']);
        }

        return [
            'score' => $score,
            'rubric_score' => round($rubricScore, 2),
            'keyword_score' => $keywordScore,
            'similarity_score' => $similarityScore,
            'feedback' => $feedback,
        ];
    }

    private function scoreWithGemini(?string $referenceAnswer, string $studentAnswer): ?array
    {
        if (empty($referenceAnswer) || empty($studentAnswer)) {
            return null;
        }

        $prompt = "Anda adalah seorang guru yang sedang memeriksa jawaban siswa.\n".
                  "Kriteria Jawaban Benar (Referensi):\n".$referenceAnswer."\n\n".
                  "Jawaban Siswa:\n".$studentAnswer."\n\n".
                  "Tugas Anda:\n".
                  "1. Berikan nilai (0 sampai 100) berdasarkan seberapa relevan dan tepat jawaban siswa dibandingkan dengan referensi.\n".
                  "2. Berikan komentar/feedback singkat (maksimal 2 kalimat).\n".
                  "Format output harus JSON persis seperti ini (tanpa markdown):\n".
                  '{"score": 85, "feedback": "Komentar di sini."}';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.config('services.gemini.key'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text', '');
                // Clean markdown code blocks if any
                $text = trim(str_replace(['```json', '```'], '', $text));

                $result = json_decode($text, true);
                if (isset($result['score']) && isset($result['feedback'])) {
                    return [
                        'score' => (float) $result['score'],
                        'feedback' => (string) $result['feedback'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini AI Scoring Error: '.$e->getMessage());
        }

        return null;
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
