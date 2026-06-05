<?php

namespace App\Livewire\Admin\Games;

use App\Models\EducationalGame;
use App\Services\Games\GameReportService;
use Livewire\Component;

class ManageGames extends Component
{
    public function toggleGame(int $gameId): void
    {
        $game = EducationalGame::findOrFail($gameId);
        $game->update(['is_active' => ! $game->is_active]);

        session()->flash('status', 'Status game berhasil diperbarui.');
    }

    public function render()
    {
        $reportService = app(GameReportService::class);

        return view('livewire.admin.games.manage-games', [
            'games' => $reportService->gamesWithStats(),
            'summary' => $reportService->summary(),
            'studentCount' => $reportService->studentCount(),
        ]);
    }
}
