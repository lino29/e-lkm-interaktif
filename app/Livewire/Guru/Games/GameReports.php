<?php

namespace App\Livewire\Guru\Games;

use App\Models\EducationalGame;
use App\Models\GameItem;
use App\Services\Games\GameReportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class GameReports extends Component
{
    use WithFileUploads;

    public string $activeTab = 'editor';

    public ?int $game_id = null;

    public string $attempt_status = '';

    public string $search = '';

    public ?int $editingGameId = null;

    public ?int $selectedGameId = null;

    public string $gameCode = '';

    public string $gameSlug = '';

    public string $gameTitle = '';

    public string $gameType = 'timed_quiz';

    public string $gameIcon = 'GM';

    public ?string $gameDescription = null;

    public ?string $gameConfig = null;

    public bool $gameIsActive = true;

    public int $gameSortOrder = 1;

    public ?int $editingItemId = null;

    public string $itemType = 'question';

    public ?string $itemPrompt = null;

    public ?string $itemQuestionText = null;

    public ?string $itemMediaUrl = null;

    public ?string $itemMediaPath = null;

    public ?string $itemOptions = null;

    public ?string $itemCorrectAnswer = null;

    public ?string $itemExplanation = null;

    public float $itemScore = 10;

    public ?int $itemTimeLimitSeconds = 10;

    public int $itemSortOrder = 1;

    public ?string $itemConfig = null;

    public bool $itemIsActive = true;

    public mixed $itemImage = null;

    /**
     * @var array<string, string>
     */
    public array $quizOptions = [
        'A' => '',
        'B' => '',
        'C' => '',
        'D' => '',
    ];

    public string $quizCorrectKey = 'A';

    /**
     * @var array<int, array{key: string, text: string, score_delta: int, feedback: string}>
     */
    public array $decisionChoices = [];

    /**
     * @var array<int, array{key: string, text: string}>
     */
    public array $puzzleComponents = [];

    public ?string $itemHint = null;

    public int $itemHintPenalty = 5;

    public function mount(): void
    {
        if (request()->routeIs('guru.games.reports')) {
            $this->activeTab = 'report';
        }

        $this->resetGameForm();
        $this->selectFirstGame();
    }

    public function createGame(): void
    {
        $this->resetGameForm();
        $this->selectedGameId = null;
        $this->resetItemForm();
        $this->activeTab = 'editor';
    }

    public function editGame(int $gameId): void
    {
        $game = EducationalGame::findOrFail($gameId);

        $this->fillGameForm($game);
        $this->resetItemForm();
        $this->activeTab = 'editor';
    }

    public function saveGame(): void
    {
        $this->normalizeGameIdentifiers();

        $validated = $this->validate([
            'gameCode' => ['required', 'string', 'max:255', Rule::unique('educational_games', 'code')->ignore($this->editingGameId)],
            'gameSlug' => ['required', 'string', 'max:255', Rule::unique('educational_games', 'slug')->ignore($this->editingGameId)],
            'gameTitle' => ['required', 'string', 'max:255'],
            'gameType' => ['required', Rule::in($this->gameTypeChoices())],
            'gameIcon' => ['nullable', 'string', 'max:20'],
            'gameDescription' => ['nullable', 'string'],
            'gameConfig' => ['nullable', 'string'],
            'gameIsActive' => ['boolean'],
            'gameSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $config = $this->decodedJson($this->gameConfig, 'gameConfig');

        if ($this->getErrorBag()->has('gameConfig')) {
            return;
        }

        $game = $this->editingGameId
            ? EducationalGame::findOrFail($this->editingGameId)
            : new EducationalGame;

        $game->fill([
            'code' => $validated['gameCode'],
            'slug' => $validated['gameSlug'],
            'title' => $validated['gameTitle'],
            'type' => $validated['gameType'],
            'icon' => $validated['gameIcon'] ?: 'GM',
            'description' => $validated['gameDescription'],
            'config' => $config,
            'is_active' => $validated['gameIsActive'],
            'sort_order' => $validated['gameSortOrder'],
        ])->save();

        $this->editGame($game->id);
        session()->flash('status', 'Game berhasil disimpan.');
    }

    public function deleteGame(int $gameId): void
    {
        $game = EducationalGame::withCount('attempts')->findOrFail($gameId);

        if ($game->attempts_count > 0) {
            $game->update(['is_active' => false]);
            session()->flash('status', 'Game memiliki riwayat attempt, jadi dinonaktifkan agar data laporan tetap aman.');
        } else {
            $game->items()->get()->each(fn (GameItem $item) => $this->deleteStoredItemImage($item->media_path));
            $game->delete();
            session()->flash('status', 'Game berhasil dihapus.');
        }

        $this->resetGameForm();
        $this->selectFirstGame();
    }

    public function selectGame(int|string $gameId): void
    {
        if (blank($gameId)) {
            $this->createGame();

            return;
        }

        $game = EducationalGame::findOrFail((int) $gameId);

        $this->fillGameForm($game);
        $this->resetItemForm();
    }

    public function createItem(): void
    {
        $this->resetItemForm();
    }

    public function editItem(int $itemId): void
    {
        $item = GameItem::where('educational_game_id', $this->selectedGameId)->findOrFail($itemId);

        $this->editingItemId = $item->id;
        $this->itemType = $item->item_type;
        $this->itemPrompt = $item->prompt;
        $this->itemQuestionText = $item->question_text;
        $this->itemMediaUrl = $item->media_url;
        $this->itemMediaPath = $item->media_path;
        $this->itemOptions = $item->options ? json_encode($item->options, JSON_PRETTY_PRINT) : null;
        $this->itemCorrectAnswer = $item->correct_answer ? json_encode($item->correct_answer, JSON_PRETTY_PRINT) : null;
        $this->itemExplanation = $item->explanation;
        $this->itemScore = (float) $item->score;
        $this->itemTimeLimitSeconds = $item->time_limit_seconds;
        $this->itemSortOrder = $item->sort_order;
        $this->itemConfig = $item->config ? json_encode($item->config, JSON_PRETTY_PRINT) : null;
        $this->itemIsActive = $item->is_active;
        $this->itemImage = null;
        $this->syncSimpleItemFields(
            $item->options,
            $item->correct_answer,
            $item->config,
            $this->gameForSelection()?->type,
        );
        $this->activeTab = 'editor';
    }

    public function updatedItemImage(): void
    {
        $this->validateOnly('itemImage', [
            'itemImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    public function saveItem(): void
    {
        if (! $this->selectedGameId) {
            $this->addError('selectedGameId', 'Pilih game terlebih dahulu.');

            return;
        }

        $this->syncItemJsonFromSimpleFields();

        $validated = $this->validate([
            'itemType' => ['required', Rule::in(['component', 'question', 'scenario', 'image_question'])],
            'itemPrompt' => ['nullable', 'string'],
            'itemQuestionText' => ['nullable', 'string'],
            'itemMediaUrl' => ['nullable', 'string', 'max:255'],
            'itemOptions' => ['nullable', 'string'],
            'itemCorrectAnswer' => ['nullable', 'string'],
            'itemExplanation' => ['nullable', 'string'],
            'itemScore' => ['required', 'numeric', 'min:0', 'max:999'],
            'itemTimeLimitSeconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'itemSortOrder' => ['required', 'integer', 'min:0'],
            'itemConfig' => ['nullable', 'string'],
            'itemIsActive' => ['boolean'],
            'itemImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $options = $this->decodedJson($this->itemOptions, 'itemOptions');
        $correctAnswer = $this->decodedJson($this->itemCorrectAnswer, 'itemCorrectAnswer');
        $config = $this->decodedJson($this->itemConfig, 'itemConfig');

        if ($this->getErrorBag()->has('itemOptions') || $this->getErrorBag()->has('itemCorrectAnswer') || $this->getErrorBag()->has('itemConfig')) {
            return;
        }

        $item = $this->editingItemId
            ? GameItem::where('educational_game_id', $this->selectedGameId)->findOrFail($this->editingItemId)
            : new GameItem(['educational_game_id' => $this->selectedGameId]);

        $mediaPath = $this->itemMediaPath;

        if ($this->itemImage) {
            $mediaPath = $this->itemImage->store('game-items', 'public');
            $this->deleteStoredItemImage($item->media_path);
        }

        $item->fill([
            'educational_game_id' => $this->selectedGameId,
            'item_type' => $validated['itemType'],
            'prompt' => $validated['itemPrompt'],
            'question_text' => $validated['itemQuestionText'],
            'media_path' => $mediaPath,
            'media_url' => $validated['itemMediaUrl'],
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'explanation' => $validated['itemExplanation'],
            'score' => $validated['itemScore'],
            'time_limit_seconds' => $validated['itemTimeLimitSeconds'],
            'sort_order' => $validated['itemSortOrder'],
            'config' => $config,
            'is_active' => $validated['itemIsActive'],
        ])->save();

        $this->editItem($item->id);
        session()->flash('status', 'Item game berhasil disimpan.');
    }

    public function deleteItem(int $itemId): void
    {
        $item = GameItem::withCount('answers')
            ->where('educational_game_id', $this->selectedGameId)
            ->findOrFail($itemId);

        if ($item->answers_count > 0) {
            $item->update(['is_active' => false]);
            session()->flash('status', 'Item memiliki jawaban murid, jadi dinonaktifkan agar histori tetap aman.');
        } else {
            $this->deleteStoredItemImage($item->media_path);
            $item->delete();
            session()->flash('status', 'Item game berhasil dihapus.');
        }

        $this->resetItemForm();
    }

    public function applyItemTemplate(): void
    {
        $type = $this->gameForSelection()?->type ?? $this->gameType;

        $this->itemOptions = json_encode($this->defaultOptionsForType($type), JSON_PRETTY_PRINT);
        $this->itemCorrectAnswer = json_encode($this->defaultCorrectAnswerForType($type), JSON_PRETTY_PRINT);
        $this->itemConfig = json_encode($this->defaultConfigForType($type), JSON_PRETTY_PRINT);
        $this->syncSimpleItemFields(
            $this->defaultOptionsForType($type),
            $this->defaultCorrectAnswerForType($type),
            $this->defaultConfigForType($type),
            $type,
        );
    }

    public function render()
    {
        $reportService = app(GameReportService::class);
        $attemptsQuery = $reportService->attemptQuery($this->game_id, $this->search)
            ->when($this->attempt_status !== '', fn ($query) => $query->where('status', $this->attempt_status));
        $games = $reportService->gamesWithStats();
        $selectedGame = $this->selectedGameId
            ? EducationalGame::with(['items' => fn ($query) => $query->orderBy('sort_order')])->find($this->selectedGameId)
            : null;

        return view('livewire.guru.games.game-reports', [
            'games' => $games,
            'selectedGame' => $selectedGame,
            'summary' => $reportService->summary($this->game_id, $this->search),
            'attempts' => $attemptsQuery
                ->latest()
                ->limit(50)
                ->get(),
            'gameTypeChoices' => $this->gameTypeChoices(),
            'gameTypeLabels' => $this->gameTypeLabels(),
        ]);
    }

    private function resetGameForm(): void
    {
        $this->editingGameId = null;
        $this->gameCode = '';
        $this->gameSlug = '';
        $this->gameTitle = '';
        $this->gameType = 'timed_quiz';
        $this->gameIcon = 'GM';
        $this->gameDescription = null;
        $this->gameConfig = json_encode(['allow_replay' => true], JSON_PRETTY_PRINT);
        $this->gameIsActive = true;
        $this->gameSortOrder = (int) EducationalGame::max('sort_order') + 1;
    }

    private function resetItemForm(): void
    {
        $game = $this->gameForSelection();

        $this->editingItemId = null;
        $this->itemType = match ($game?->type) {
            'puzzle_order' => 'component',
            'decision_mission' => 'scenario',
            'image_guess' => 'image_question',
            default => 'question',
        };
        $this->itemPrompt = null;
        $this->itemQuestionText = null;
        $this->itemMediaUrl = null;
        $this->itemMediaPath = null;
        $this->itemOptions = json_encode($this->defaultOptionsForType($game?->type ?? $this->gameType), JSON_PRETTY_PRINT);
        $this->itemCorrectAnswer = json_encode($this->defaultCorrectAnswerForType($game?->type ?? $this->gameType), JSON_PRETTY_PRINT);
        $this->itemExplanation = null;
        $this->itemScore = $game?->type === 'image_guess' ? 20 : 10;
        $this->itemTimeLimitSeconds = $game?->type === 'timed_quiz' ? 10 : null;
        $this->itemSortOrder = $game ? ((int) $game->items()->max('sort_order') + 1) : 1;
        $this->itemConfig = json_encode($this->defaultConfigForType($game?->type ?? $this->gameType), JSON_PRETTY_PRINT);
        $this->itemIsActive = true;
        $this->itemImage = null;
        $this->syncSimpleItemFields(
            $this->defaultOptionsForType($game?->type ?? $this->gameType),
            $this->defaultCorrectAnswerForType($game?->type ?? $this->gameType),
            $this->defaultConfigForType($game?->type ?? $this->gameType),
            $game?->type ?? $this->gameType,
        );
    }

    private function selectFirstGame(): void
    {
        $game = EducationalGame::orderBy('sort_order')->first();
        $this->selectedGameId = $game?->id;

        if ($game) {
            $this->fillGameForm($game);
        }

        $this->resetItemForm();
    }

    private function fillGameForm(EducationalGame $game): void
    {
        $this->editingGameId = $game->id;
        $this->selectedGameId = $game->id;
        $this->gameCode = $game->code;
        $this->gameSlug = $game->slug;
        $this->gameTitle = $game->title;
        $this->gameType = $game->type;
        $this->gameIcon = $game->icon ?? 'GM';
        $this->gameDescription = $game->description;
        $this->gameConfig = $game->config ? json_encode($game->config, JSON_PRETTY_PRINT) : null;
        $this->gameIsActive = $game->is_active;
        $this->gameSortOrder = $game->sort_order;
    }

    /**
     * @return array<int, string>
     */
    private function gameTypeChoices(): array
    {
        return ['puzzle_order', 'timed_quiz', 'decision_mission', 'image_guess'];
    }

    /**
     * @return array<string, string>
     */
    private function gameTypeLabels(): array
    {
        return [
            'timed_quiz' => 'Kuis cepat',
            'image_guess' => 'Tebak gambar',
            'decision_mission' => 'Misi pilihan',
            'puzzle_order' => 'Susun urutan',
        ];
    }

    private function gameForSelection(): ?EducationalGame
    {
        return $this->selectedGameId ? EducationalGame::find($this->selectedGameId) : null;
    }

    private function normalizeGameIdentifiers(): void
    {
        $baseSlug = Str::slug($this->gameTitle) ?: 'game';
        $baseCode = Str::of($baseSlug)->replace('-', '_')->toString();

        if (blank($this->gameCode)) {
            $this->gameCode = $this->uniqueGameValue('code', $baseCode);
        }

        if (blank($this->gameSlug)) {
            $this->gameSlug = $this->uniqueGameValue('slug', $baseSlug);
        }

        if (blank($this->gameIcon)) {
            $this->gameIcon = match ($this->gameType) {
                'puzzle_order' => 'PZL',
                'decision_mission' => 'MSI',
                'image_guess' => 'IMG',
                default => 'KUIS',
            };
        }
    }

    private function uniqueGameValue(string $column, string $baseValue): string
    {
        $candidate = $baseValue;
        $suffix = 2;

        while (
            EducationalGame::where($column, $candidate)
                ->when($this->editingGameId !== null, fn ($query) => $query->whereKeyNot($this->editingGameId))
                ->exists()
        ) {
            $candidate = $column === 'code'
                ? $baseValue.'_'.$suffix
                : $baseValue.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function decodedJson(?string $json, string $field): ?array
    {
        if (blank($json)) {
            return null;
        }

        $decoded = json_decode((string) $json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->addError($field, 'Format JSON tidak valid.');

            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>|null  $options
     * @param  array<string, mixed>|null  $correctAnswer
     * @param  array<string, mixed>|null  $config
     */
    private function syncSimpleItemFields(?array $options, ?array $correctAnswer, ?array $config, ?string $type): void
    {
        if ($type === 'decision_mission') {
            $choices = collect($options ?: $this->defaultOptionsForType($type))
                ->values()
                ->map(fn (array $choice, int $index): array => [
                    'key' => (string) ($choice['key'] ?? chr(65 + $index)),
                    'text' => (string) ($choice['text'] ?? ''),
                    'score_delta' => (int) ($choice['score_delta'] ?? 0),
                    'feedback' => (string) ($choice['feedback'] ?? ''),
                ])
                ->pad(3, ['key' => '', 'text' => '', 'score_delta' => 0, 'feedback' => ''])
                ->take(3)
                ->values()
                ->all();

            foreach ($choices as $index => $choice) {
                $choices[$index]['key'] = $choice['key'] ?: chr(65 + $index);
            }

            $this->decisionChoices = $choices;

            return;
        }

        if ($type === 'puzzle_order') {
            $this->puzzleComponents = collect($options ?: $this->defaultOptionsForType($type))
                ->map(fn (string $text, string $key): array => [
                    'key' => $key,
                    'text' => $text,
                ])
                ->values()
                ->pad(4, ['key' => '', 'text' => ''])
                ->take(4)
                ->values()
                ->all();

            return;
        }

        $preparedOptions = collect($options ?: $this->defaultOptionsForType($type))
            ->mapWithKeys(fn (string $text, string $key): array => [$key => $text])
            ->all();

        $this->quizOptions = array_slice($preparedOptions, 0, 4, true);

        foreach (['A', 'B', 'C', 'D'] as $fallbackKey) {
            if (count($this->quizOptions) >= 4) {
                break;
            }

            $this->quizOptions[$fallbackKey] ??= '';
        }
        $this->quizCorrectKey = (string) data_get($correctAnswer, 'key', array_key_first($this->quizOptions) ?: 'A');
        $this->itemHint = (string) data_get($config, 'hint', '');
        $this->itemHintPenalty = (int) data_get($config, 'hint_penalty', 5);
    }

    private function syncItemJsonFromSimpleFields(): void
    {
        $type = $this->gameForSelection()?->type ?? $this->gameType;

        if ($type === 'decision_mission') {
            $choices = collect($this->decisionChoices)
                ->map(function (array $choice, int $index): ?array {
                    $text = trim((string) ($choice['text'] ?? ''));

                    if ($text === '') {
                        return null;
                    }

                    return [
                        'key' => (string) ($choice['key'] ?: chr(65 + $index)),
                        'text' => $text,
                        'score_delta' => (int) ($choice['score_delta'] ?? 0),
                        'feedback' => trim((string) ($choice['feedback'] ?? '')),
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $this->itemType = 'scenario';
            $this->itemOptions = json_encode($choices, JSON_PRETTY_PRINT);
            $this->itemCorrectAnswer = null;
            $this->itemConfig = json_encode([], JSON_PRETTY_PRINT);
            $this->itemTimeLimitSeconds = null;

            if ($choices !== []) {
                $this->itemScore = max(array_column($choices, 'score_delta'));
            }

            return;
        }

        if ($type === 'puzzle_order') {
            $components = collect($this->puzzleComponents)
                ->map(function (array $component): ?array {
                    $text = trim((string) ($component['text'] ?? ''));

                    if ($text === '') {
                        return null;
                    }

                    $key = trim((string) ($component['key'] ?? '')) ?: Str::slug($text, '_');

                    return ['key' => $key, 'text' => $text];
                })
                ->filter()
                ->values();
            $options = $components->mapWithKeys(fn (array $component): array => [$component['key'] => $component['text']])->all();

            $this->itemType = 'component';
            $this->itemOptions = json_encode($options, JSON_PRETTY_PRINT);
            $this->itemCorrectAnswer = json_encode(['accepted_orders' => [array_keys($options)]], JSON_PRETTY_PRINT);
            $this->itemConfig = json_encode([], JSON_PRETTY_PRINT);
            $this->itemTimeLimitSeconds = null;

            return;
        }

        $options = collect($this->quizOptions)
            ->map(fn (string $text): string => trim($text))
            ->filter(fn (string $text): bool => $text !== '')
            ->all();
        $correctKey = array_key_exists($this->quizCorrectKey, $options)
            ? $this->quizCorrectKey
            : (array_key_first($options) ?: 'A');

        $this->itemType = $type === 'image_guess' ? 'image_question' : 'question';
        $this->itemOptions = json_encode($options, JSON_PRETTY_PRINT);
        $this->itemCorrectAnswer = json_encode(['key' => $correctKey], JSON_PRETTY_PRINT);
        $this->itemConfig = json_encode(
            $type === 'image_guess'
                ? ['hint' => $this->itemHint, 'hint_penalty' => $this->itemHintPenalty]
                : [],
            JSON_PRETTY_PRINT,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultOptionsForType(?string $type): array
    {
        return match ($type) {
            'puzzle_order' => [
                'panel_surya' => 'Panel Surya',
                'inverter' => 'Inverter',
                'beban_listrik' => 'Rumah/Beban Listrik',
            ],
            'decision_mission' => [
                ['key' => 'A', 'text' => 'Pilihan kurang tepat', 'score_delta' => 0, 'feedback' => 'Dampaknya belum sesuai tujuan.'],
                ['key' => 'B', 'text' => 'Pilihan cukup baik', 'score_delta' => 15, 'feedback' => 'Dampaknya mulai membantu.'],
                ['key' => 'C', 'text' => 'Pilihan terbaik', 'score_delta' => 30, 'feedback' => 'Keputusan berdampak kuat.'],
            ],
            default => [
                'A' => 'Pilihan A',
                'B' => 'Pilihan B',
                'C' => 'Pilihan C',
                'D' => 'Pilihan D',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultCorrectAnswerForType(?string $type): array
    {
        return match ($type) {
            'puzzle_order' => ['accepted_orders' => [['panel_surya', 'inverter', 'beban_listrik']]],
            'decision_mission' => [],
            default => ['key' => 'A'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultConfigForType(?string $type): array
    {
        return match ($type) {
            'image_guess' => ['hint' => 'Petunjuk singkat untuk murid.', 'hint_penalty' => 5],
            default => [],
        };
    }

    private function deleteStoredItemImage(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, 'game-items/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
