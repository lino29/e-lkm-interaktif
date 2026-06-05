<?php

namespace App\Services\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Models\GameAttemptAnswer;
use App\Models\GameItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GameAttemptService
{
    public function __construct(
        private readonly GameScoringService $scoringService,
    ) {}

    public function startAttempt(EducationalGame $game, User $student): GameAttempt
    {
        abort_unless($student->hasRole('murid'), 403);
        abort_unless($game->is_active, 404);

        return DB::transaction(function () use ($game, $student): GameAttempt {
            GameAttempt::query()
                ->where('educational_game_id', $game->id)
                ->where('user_id', $student->id)
                ->whereIn('status', ['started', 'in_progress'])
                ->get()
                ->each(function (GameAttempt $attempt): void {
                    $finishedAt = now();
                    $attempt->update([
                        'status' => 'abandoned',
                        'finished_at' => $finishedAt,
                        'duration_seconds' => $attempt->started_at?->diffInSeconds($finishedAt) ?? 0,
                    ]);
                });

            $attemptNumber = ((int) GameAttempt::query()
                ->where('educational_game_id', $game->id)
                ->where('user_id', $student->id)
                ->max('attempt_number')) + 1;

            return GameAttempt::create([
                'educational_game_id' => $game->id,
                'user_id' => $student->id,
                'status' => 'in_progress',
                'attempt_number' => $attemptNumber,
                'score' => 0,
                'max_score' => $this->scoringService->maxScoreForGame($game),
                'duration_seconds' => 0,
                'started_at' => now(),
                'metadata' => [],
            ]);
        });
    }

    public function resumeOrStart(EducationalGame $game, User $student, ?int $attemptId = null): GameAttempt
    {
        abort_unless($student->hasRole('murid'), 403);
        abort_unless($game->is_active, 404);

        if ($attemptId !== null) {
            return GameAttempt::query()
                ->where('id', $attemptId)
                ->where('educational_game_id', $game->id)
                ->where('user_id', $student->id)
                ->whereIn('status', ['started', 'in_progress'])
                ->firstOrFail();
        }

        $attempt = GameAttempt::query()
            ->where('educational_game_id', $game->id)
            ->where('user_id', $student->id)
            ->whereIn('status', ['started', 'in_progress'])
            ->latest()
            ->first();

        return $attempt ?? $this->startAttempt($game, $student);
    }

    public function answerItem(GameAttempt $attempt, GameItem $item, User $student, array $answer, ?int $timeSpentSeconds = null, bool $hintUsed = false): GameAttemptAnswer
    {
        $attempt = $attempt->fresh(['game']);

        if ($attempt->user_id !== $student->id || $attempt->status === 'finished' || $attempt->finished_at !== null) {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt game ini sudah selesai atau tidak valid.',
            ]);
        }

        if ($item->educational_game_id !== $attempt->educational_game_id || ! $item->is_active) {
            throw ValidationException::withMessages([
                'item' => 'Item game tidak valid.',
            ]);
        }

        $score = $this->scoringService->scoreItem($item->loadMissing('game'), $answer);

        return GameAttemptAnswer::updateOrCreate(
            [
                'game_attempt_id' => $attempt->id,
                'game_item_id' => $item->id,
            ],
            [
                'answer' => $answer,
                'is_correct' => $score['is_correct'],
                'score' => $score['score'],
                'time_spent_seconds' => $timeSpentSeconds,
                'hint_used' => $hintUsed,
                'feedback' => $score['feedback'],
                'answered_at' => now(),
                'metadata' => $score['metadata'],
            ],
        );
    }

    public function finishAttempt(GameAttempt $attempt, User $student): GameAttempt
    {
        $attempt = $attempt->fresh(['game.activeItems', 'answers']);

        if ($attempt->user_id !== $student->id) {
            abort(403);
        }

        if ($attempt->status === 'finished') {
            return $attempt;
        }

        $startedAt = $attempt->started_at ?? now();
        $attempt->update([
            'status' => 'finished',
            'score' => (float) $attempt->answers->sum('score'),
            'max_score' => $this->scoringService->maxScoreForGame($attempt->game),
            'duration_seconds' => max(0, $startedAt->diffInSeconds(now())),
            'finished_at' => now(),
        ]);

        return $attempt->fresh(['game', 'answers.item']);
    }
}
