<?php

namespace App\Livewire\Murid\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Services\Games\GameAttemptService;
use Livewire\Component;

class GameHub extends Component
{
    public function startGame(int $gameId, GameAttemptService $attemptService)
    {
        $game = EducationalGame::where('is_active', true)->findOrFail($gameId);
        $attempt = $attemptService->startAttempt($game, auth()->user());

        return redirect()->route('murid.games.play', [
            'game' => $game->slug,
            'attempt' => $attempt->id,
        ]);
    }

    public function render()
    {
        $games = EducationalGame::query()
            ->where('is_active', true)
            ->withCount(['attempts as finished_attempts_count' => fn ($query) => $query->where('status', 'finished')])
            ->orderBy('sort_order')
            ->get();

        $latestAttempts = GameAttempt::query()
            ->where('user_id', auth()->id())
            ->whereIn('educational_game_id', $games->pluck('id'))
            ->where('status', 'finished')
            ->latest('finished_at')
            ->get()
            ->unique('educational_game_id')
            ->keyBy('educational_game_id');

        return view('livewire.murid.games.game-hub', [
            'games' => $games,
            'latestAttempts' => $latestAttempts,
        ]);
    }
}
