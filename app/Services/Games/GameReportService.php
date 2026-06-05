<?php

namespace App\Services\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GameReportService
{
    /**
     * @return array{total_attempts: int, finished_attempts: int, player_count: int, average_score: float|null, highest_score: float|null}
     */
    public function summary(?int $gameId = null, string $search = ''): array
    {
        $query = $this->attemptQuery($gameId, $search);
        $finishedQuery = (clone $query)->where('status', 'finished');

        return [
            'total_attempts' => (clone $query)->count(),
            'finished_attempts' => (clone $finishedQuery)->count(),
            'player_count' => (clone $query)->distinct('user_id')->count('user_id'),
            'average_score' => ($average = (clone $finishedQuery)->avg('score')) === null ? null : round((float) $average, 2),
            'highest_score' => ($highest = (clone $finishedQuery)->max('score')) === null ? null : round((float) $highest, 2),
        ];
    }

    public function attemptQuery(?int $gameId = null, string $search = ''): Builder
    {
        return GameAttempt::query()
            ->with('game', 'user')
            ->when($gameId !== null, fn (Builder $query) => $query->where('educational_game_id', $gameId))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('user', function (Builder $userQuery) use ($search) {
                $userQuery->where('name', 'like', '%'.$search.'%');
            }));
    }

    public function gamesWithStats()
    {
        return EducationalGame::query()
            ->withCount('items')
            ->withCount('attempts')
            ->withAvg(['attempts as finished_average_score' => fn (Builder $query) => $query->where('status', 'finished')], 'score')
            ->withMax(['attempts as finished_highest_score' => fn (Builder $query) => $query->where('status', 'finished')], 'score')
            ->orderBy('sort_order')
            ->get();
    }

    public function studentCount(): int
    {
        return User::role('murid')->count();
    }
}
