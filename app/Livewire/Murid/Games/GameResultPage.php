<?php

namespace App\Livewire\Murid\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use Livewire\Component;

class GameResultPage extends Component
{
    public EducationalGame $currentGame;

    public GameAttempt $currentAttempt;

    public function mount(string $game, string|int $attempt): void
    {
        $this->currentGame = EducationalGame::where('slug', $game)->firstOrFail();

        $this->currentAttempt = GameAttempt::query()
            ->with('answers.item')
            ->where('id', $attempt)
            ->where('educational_game_id', $this->currentGame->id)
            ->where('user_id', auth()->id())
            ->where('status', 'finished')
            ->firstOrFail();
    }

    public function render()
    {
        $percentage = (float) $this->currentAttempt->max_score > 0
            ? round(((float) $this->currentAttempt->score / (float) $this->currentAttempt->max_score) * 100, 1)
            : 0;

        return view('livewire.murid.games.game-result-page', [
            'game' => $this->currentGame,
            'attempt' => $this->currentAttempt,
            'percentage' => $percentage,
        ]);
    }
}
