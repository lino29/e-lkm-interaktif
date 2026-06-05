<?php

namespace App\Livewire\Murid\Games;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Models\GameAttemptAnswer;
use App\Models\GameItem;
use App\Services\Games\GameAttemptService;
use Illuminate\Support\Collection;
use Livewire\Component;

class GamePlayPage extends Component
{
    public EducationalGame $currentGame;

    public GameAttempt $currentAttempt;

    /**
     * @var array<int, string>
     */
    public array $puzzleOrder = [];

    public int $currentItemIndex = 0;

    public int $questionStartedAt = 0;

    public ?string $selectedOption = null;

    /**
     * @var array<int, bool>
     */
    public array $hintUsed = [];

    public ?string $lastFeedback = null;

    public ?float $lastScore = null;

    public ?bool $lastAnswerCorrect = null;

    public ?string $selectedAnswerKey = null;

    public bool $awaitingContinue = false;

    public ?int $answeredItemId = null;

    public function mount(string $game, GameAttemptService $attemptService): void
    {
        $this->currentGame = EducationalGame::query()
            ->with('activeItems')
            ->where('slug', $game)
            ->where('is_active', true)
            ->firstOrFail();

        abort_if($this->currentGame->activeItems->isEmpty(), 404);

        $this->currentAttempt = $attemptService->resumeOrStart(
            $this->currentGame,
            auth()->user(),
            request()->integer('attempt') ?: null,
        );

        $this->initializeState();
    }

    public function movePuzzleItem(int $index, string $direction): void
    {
        if (! isset($this->puzzleOrder[$index])) {
            return;
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($this->puzzleOrder[$targetIndex])) {
            return;
        }

        [$this->puzzleOrder[$index], $this->puzzleOrder[$targetIndex]] = [$this->puzzleOrder[$targetIndex], $this->puzzleOrder[$index]];
        $this->puzzleOrder = array_values($this->puzzleOrder);
    }

    public function submitPuzzle(GameAttemptService $attemptService)
    {
        $item = $this->activeItems()->firstOrFail();

        $attemptService->answerItem(
            $this->currentAttempt,
            $item,
            auth()->user(),
            ['order' => array_values($this->puzzleOrder)],
        );

        $finishedAttempt = $attemptService->finishAttempt($this->currentAttempt, auth()->user());

        return redirect()->route('murid.games.result', [
            'game' => $this->currentGame->slug,
            'attempt' => $finishedAttempt->id,
        ]);
    }

    public function submitQuizAnswer(?string $selected, GameAttemptService $attemptService)
    {
        if ($this->awaitingContinue) {
            return null;
        }

        $item = $this->currentItem();
        $elapsedSeconds = $this->elapsedSeconds($item);
        $timedOut = $elapsedSeconds > (int) ($item->time_limit_seconds ?? 10);
        $this->selectedAnswerKey = $selected;

        $answer = $attemptService->answerItem(
            $this->currentAttempt,
            $item,
            auth()->user(),
            [
                'selected' => $timedOut ? null : $selected,
                'timed_out' => $timedOut,
            ],
            min($elapsedSeconds, (int) ($item->time_limit_seconds ?? $elapsedSeconds)),
        );

        $this->holdFeedback($answer);

        return null;
    }

    public function timeoutQuizAnswer(GameAttemptService $attemptService)
    {
        if ($this->awaitingContinue) {
            return null;
        }

        $item = $this->currentItem();
        $this->selectedAnswerKey = null;

        $answer = $attemptService->answerItem(
            $this->currentAttempt,
            $item,
            auth()->user(),
            [
                'selected' => null,
                'timed_out' => true,
            ],
            (int) ($item->time_limit_seconds ?? 10),
        );

        $this->holdFeedback($answer);

        return null;
    }

    public function chooseDecision(string $choice, GameAttemptService $attemptService)
    {
        if ($this->awaitingContinue) {
            return null;
        }

        $item = $this->currentItem();
        $this->selectedAnswerKey = $choice;

        $answer = $attemptService->answerItem(
            $this->currentAttempt,
            $item,
            auth()->user(),
            ['choice' => $choice],
        );

        $this->holdFeedback($answer);

        return null;
    }

    public function revealHint(int $itemId): void
    {
        $item = $this->currentItem();

        if ($item->id === $itemId) {
            $this->hintUsed[$itemId] = true;
        }
    }

    public function chooseImageAnswer(string $selected, GameAttemptService $attemptService)
    {
        if ($this->awaitingContinue) {
            return null;
        }

        $item = $this->currentItem();
        $hintUsed = (bool) ($this->hintUsed[$item->id] ?? false);
        $this->selectedAnswerKey = $selected;

        $answer = $attemptService->answerItem(
            $this->currentAttempt,
            $item,
            auth()->user(),
            [
                'selected' => $selected,
                'hint_used' => $hintUsed,
            ],
            null,
            $hintUsed,
        );

        $this->holdFeedback($answer);

        return null;
    }

    public function continueAfterFeedback(GameAttemptService $attemptService)
    {
        if (! $this->awaitingContinue) {
            return null;
        }

        return $this->advanceOrFinish($attemptService);
    }

    public function render()
    {
        $items = $this->activeItems()->values();
        $currentItem = $items->get($this->currentItemIndex);

        return view('livewire.murid.games.game-play-page', [
            'game' => $this->currentGame,
            'attempt' => $this->currentAttempt,
            'items' => $items,
            'currentItem' => $currentItem,
            'progressLabel' => $currentItem ? ($this->currentItemIndex + 1).' / '.$items->count() : '-',
        ]);
    }

    private function initializeState(): void
    {
        $items = $this->activeItems()->values();
        $answeredCount = $this->currentAttempt->answers()->count();

        $this->currentItemIndex = min($answeredCount, max(0, $items->count() - 1));

        if ($this->currentGame->type === 'puzzle_order') {
            $answer = $this->currentAttempt->answers()->latest()->first()?->answer;
            $this->puzzleOrder = is_array($answer) && isset($answer['order'])
                ? array_values((array) $answer['order'])
                : array_keys((array) $items->first()?->options);

            if ($answer === null) {
                shuffle($this->puzzleOrder);
            }
        }

        if (in_array($this->currentGame->type, ['timed_quiz', 'decision_mission', 'image_guess'], true)) {
            $this->startCurrentTimer();
        }

        $this->hintUsed = $this->currentAttempt->answers()
            ->where('hint_used', true)
            ->pluck('hint_used', 'game_item_id')
            ->map(fn (bool $value): bool => $value)
            ->all();
    }

    /**
     * @return Collection<int, GameItem>
     */
    private function activeItems(): Collection
    {
        return $this->currentGame->activeItems()->get();
    }

    private function currentItem(): GameItem
    {
        return $this->activeItems()->values()->get($this->currentItemIndex) ?? abort(404);
    }

    private function startCurrentTimer(): void
    {
        $this->questionStartedAt = now()->timestamp;
    }

    private function elapsedSeconds(GameItem $item): int
    {
        if ($this->questionStartedAt <= 0) {
            return (int) ($item->time_limit_seconds ?? 0);
        }

        return max(0, now()->timestamp - $this->questionStartedAt);
    }

    private function advanceOrFinish(GameAttemptService $attemptService)
    {
        $itemsCount = $this->activeItems()->count();

        if ($this->currentItemIndex >= $itemsCount - 1) {
            $finishedAttempt = $attemptService->finishAttempt($this->currentAttempt, auth()->user());

            return redirect()->route('murid.games.result', [
                'game' => $this->currentGame->slug,
                'attempt' => $finishedAttempt->id,
            ]);
        }

        $this->currentItemIndex++;
        $this->selectedOption = null;
        $this->selectedAnswerKey = null;
        $this->lastFeedback = null;
        $this->lastScore = null;
        $this->lastAnswerCorrect = null;
        $this->answeredItemId = null;
        $this->awaitingContinue = false;
        $this->startCurrentTimer();
        $this->currentAttempt = $this->currentAttempt->fresh();

        return null;
    }

    private function holdFeedback(GameAttemptAnswer $answer): void
    {
        $this->lastFeedback = $answer->feedback;
        $this->lastScore = (float) $answer->score;
        $this->lastAnswerCorrect = $answer->is_correct;
        $this->answeredItemId = $answer->game_item_id;
        $this->awaitingContinue = true;
        $this->currentAttempt = $this->currentAttempt->fresh();
    }
}
