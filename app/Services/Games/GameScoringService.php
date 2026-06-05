<?php

namespace App\Services\Games;

use App\Models\EducationalGame;
use App\Models\GameItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class GameScoringService
{
    /**
     * @return array{is_correct: bool, score: float, max_score: float, feedback: string, metadata: array<string, mixed>}
     */
    public function scoreItem(GameItem $item, array $answer): array
    {
        return match ($item->game->type) {
            'puzzle_order' => $this->scorePuzzleOrder($item, $answer),
            'timed_quiz' => $this->scoreTimedQuiz($item, $answer),
            'decision_mission' => $this->scoreDecisionMission($item, $answer),
            'image_guess' => $this->scoreImageGuess($item, $answer),
            default => [
                'is_correct' => false,
                'score' => 0.0,
                'max_score' => (float) $item->score,
                'feedback' => 'Tipe game belum didukung.',
                'metadata' => [],
            ],
        };
    }

    public function maxScoreForGame(EducationalGame $game): float
    {
        return (float) $game->activeItems()->sum('score');
    }

    /**
     * @return array{is_correct: bool, score: float, max_score: float, feedback: string, metadata: array<string, mixed>}
     */
    private function scorePuzzleOrder(GameItem $item, array $answer): array
    {
        $studentOrder = array_values(array_map('strval', (array) ($answer['order'] ?? [])));
        $acceptedOrders = (array) data_get($item->correct_answer, 'accepted_orders', []);
        $isCorrect = collect($acceptedOrders)
            ->contains(fn (array $acceptedOrder): bool => array_values(array_map('strval', $acceptedOrder)) === $studentOrder);

        return [
            'is_correct' => $isCorrect,
            'score' => $isCorrect ? (float) $item->score : 0.0,
            'max_score' => (float) $item->score,
            'feedback' => $isCorrect ? ($item->explanation ?? 'Urutan sudah tepat.') : 'Urutan belum tepat. Perhatikan aliran energi dari sumber ke beban listrik.',
            'metadata' => [
                'accepted_orders' => $acceptedOrders,
            ],
        ];
    }

    /**
     * @return array{is_correct: bool, score: float, max_score: float, feedback: string, metadata: array<string, mixed>}
     */
    private function scoreTimedQuiz(GameItem $item, array $answer): array
    {
        $selected = $answer['selected'] ?? null;
        $timedOut = (bool) ($answer['timed_out'] ?? false);
        $this->ensureValidOption($item, $selected, allowNull: $timedOut);

        $correctKey = (string) data_get($item->correct_answer, 'key', Arr::first((array) $item->correct_answer));
        $isCorrect = ! $timedOut && (string) $selected === $correctKey;

        return [
            'is_correct' => $isCorrect,
            'score' => $isCorrect ? (float) $item->score : 0.0,
            'max_score' => (float) $item->score,
            'feedback' => $timedOut
                ? 'Waktu habis untuk soal ini.'
                : ($isCorrect ? ($item->explanation ?? 'Jawaban benar.') : 'Jawaban belum tepat.'),
            'metadata' => [
                'correct_key' => $correctKey,
                'timed_out' => $timedOut,
            ],
        ];
    }

    /**
     * @return array{is_correct: bool, score: float, max_score: float, feedback: string, metadata: array<string, mixed>}
     */
    private function scoreDecisionMission(GameItem $item, array $answer): array
    {
        $selected = $answer['choice'] ?? null;
        $choice = collect((array) $item->options)->firstWhere('key', $selected);

        if (! is_array($choice)) {
            throw ValidationException::withMessages([
                'choice' => 'Pilihan misi tidak valid.',
            ]);
        }

        $score = max(0.0, min((float) $item->score, (float) ($choice['score_delta'] ?? 0)));

        return [
            'is_correct' => $score >= (float) $item->score,
            'score' => $score,
            'max_score' => (float) $item->score,
            'feedback' => (string) ($choice['feedback'] ?? 'Pilihan tersimpan.'),
            'metadata' => [
                'choice_text' => $choice['text'] ?? null,
                'score_delta' => $score,
            ],
        ];
    }

    /**
     * @return array{is_correct: bool, score: float, max_score: float, feedback: string, metadata: array<string, mixed>}
     */
    private function scoreImageGuess(GameItem $item, array $answer): array
    {
        $selected = $answer['selected'] ?? null;
        $this->ensureValidOption($item, $selected);

        $hintUsed = (bool) ($answer['hint_used'] ?? false);
        $correctKey = (string) data_get($item->correct_answer, 'key', Arr::first((array) $item->correct_answer));
        $hintPenalty = (float) data_get($item->config, 'hint_penalty', 5);
        $isCorrect = (string) $selected === $correctKey;

        return [
            'is_correct' => $isCorrect,
            'score' => $isCorrect ? max(0.0, (float) $item->score - ($hintUsed ? $hintPenalty : 0.0)) : 0.0,
            'max_score' => (float) $item->score,
            'feedback' => $isCorrect ? ($item->explanation ?? 'Tebakan benar.') : 'Tebakan belum tepat.',
            'metadata' => [
                'correct_key' => $correctKey,
                'hint_penalty' => $hintPenalty,
            ],
        ];
    }

    private function ensureValidOption(GameItem $item, mixed $selected, bool $allowNull = false): void
    {
        if ($allowNull && ($selected === null || $selected === '')) {
            return;
        }

        $validKeys = array_keys((array) $item->options);

        if (! in_array((string) $selected, array_map('strval', $validKeys), true)) {
            throw ValidationException::withMessages([
                'selected' => 'Pilihan jawaban tidak valid.',
            ]);
        }
    }
}
