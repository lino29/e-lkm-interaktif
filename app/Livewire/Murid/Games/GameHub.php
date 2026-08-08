<?php

namespace App\Livewire\Murid\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Services\Games\GameAttemptService;
use Livewire\Component;

class GameHub extends Component
{
    public string $search = '';

    public string $gameType = '';

    public function startGame(int $gameId, GameAttemptService $attemptService)
    {
        $game = EducationalGame::query()
            ->where('is_active', true)
            ->whereHas('activeItems')
            ->findOrFail($gameId);
        $attempt = $attemptService->resumeOrStart($game, auth()->user());

        return redirect()->route('murid.games.play', [
            'game' => $game->slug,
            'attempt' => $attempt->id,
        ]);
    }

    public function render()
    {
        $games = EducationalGame::query()
            ->where('is_active', true)
            ->whereHas('activeItems')
            ->when($this->gameType !== '', fn ($query) => $query->where('type', $this->gameType))
            ->when($this->search !== '', fn ($query) => $query->where(function ($searchQuery) {
                $searchQuery
                    ->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->withCount('activeItems')
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

        $activeAttempts = GameAttempt::query()
            ->where('user_id', auth()->id())
            ->whereIn('educational_game_id', $games->pluck('id'))
            ->whereIn('status', ['started', 'in_progress'])
            ->latest()
            ->get()
            ->unique('educational_game_id')
            ->keyBy('educational_game_id');

        return view('livewire.murid.games.game-hub', [
            'games' => $games,
            'latestAttempts' => $latestAttempts,
            'activeAttempts' => $activeAttempts,
            'gameTypeLabels' => [
                'timed_quiz' => 'Kuis cepat',
                'image_guess' => 'Tebak gambar',
                'decision_mission' => 'Misi pilihan',
                'puzzle_order' => 'Susun urutan',
            ],
        ]);
    }
}
