<?php

use App\Livewire\Guru\Games\GameReports;
use App\Livewire\Murid\Games\GameHub;
use App\Livewire\Murid\Games\GamePlayPage;
use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Models\GameItem;
use App\Models\User;
use App\Services\Games\GameAttemptService;
use Database\Seeders\EducationalGameSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(EducationalGameSeeder::class);

    $this->student = User::factory()->create();
    $this->student->assignRole('murid');

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('guru');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('games routes are scoped by role', function () {
    $this->actingAs($this->student)
        ->get(route('murid.games.index'))
        ->assertOk()
        ->assertSee('Games Edukatif');

    $this->actingAs($this->teacher)
        ->get(route('murid.games.index'))
        ->assertForbidden();

    $this->actingAs($this->teacher)
        ->get(route('guru.games.manage'))
        ->assertOk()
        ->assertSee('Kelola Games');

    $this->actingAs($this->teacher)
        ->get(route('guru.games.reports'))
        ->assertOk()
        ->assertSee('Attempt Terbaru');

    $this->actingAs($this->student)
        ->get(route('guru.games.manage'))
        ->assertForbidden();
});

test('guru can create a game without filling technical identifiers', function () {
    Livewire::actingAs($this->teacher)
        ->test(GameReports::class)
        ->call('createGame')
        ->set('gameTitle', 'Kuis Hemat Energi')
        ->set('gameType', 'timed_quiz')
        ->set('gameDescription', 'Latihan singkat tentang kebiasaan hemat energi.')
        ->call('saveGame')
        ->assertHasNoErrors();

    $game = EducationalGame::where('title', 'Kuis Hemat Energi')->firstOrFail();

    expect($game->code)->toBe('kuis_hemat_energi')
        ->and($game->slug)->toBe('kuis-hemat-energi')
        ->and($game->is_active)->toBeTrue();
});

test('starting a game from hub creates a new attempt', function () {
    $game = EducationalGame::where('code', 'puzzle_solar_flow')->firstOrFail();

    Livewire::actingAs($this->student)
        ->test(GameHub::class)
        ->call('startGame', $game->id);

    $attempt = GameAttempt::where('educational_game_id', $game->id)
        ->where('user_id', $this->student->id)
        ->firstOrFail();

    expect($attempt->status)->toBe('in_progress')
        ->and($attempt->attempt_number)->toBe(1)
        ->and((float) $attempt->max_score)->toBe(100.0);
});

test('puzzle game stores order answer and finishes attempt', function () {
    $game = EducationalGame::where('code', 'puzzle_solar_flow')->firstOrFail();
    $attempt = app(GameAttemptService::class)->startAttempt($game, $this->student);

    Livewire::actingAs($this->student)
        ->test(GamePlayPage::class, ['game' => $game->slug, 'attempt' => $attempt->id])
        ->set('puzzleOrder', ['panel_surya', 'baterai', 'inverter', 'beban_listrik'])
        ->call('submitPuzzle')
        ->assertRedirect(route('murid.games.result', ['game' => $game->slug, 'attempt' => $attempt->id]));

    $attempt->refresh();
    $answer = $attempt->answers()->firstOrFail();

    expect($attempt->status)->toBe('finished')
        ->and((float) $attempt->score)->toBe(100.0)
        ->and($answer->answer['order'])->toBe(['panel_surya', 'baterai', 'inverter', 'beban_listrik'])
        ->and($answer->is_correct)->toBeTrue();
});

test('backend scoring handles quiz timeout mission decision and image hint penalty', function () {
    $attemptService = app(GameAttemptService::class);

    $quiz = EducationalGame::where('code', 'quick_quiz_energy')->firstOrFail();
    $quizAttempt = $attemptService->startAttempt($quiz, $this->student);
    $quizItem = $quiz->activeItems()->firstOrFail();

    $quizAnswer = $attemptService->answerItem(
        $quizAttempt,
        $quizItem,
        $this->student,
        ['selected' => null, 'timed_out' => true],
        10,
    );

    $mission = EducationalGame::where('code', 'earth_rescue_mission')->firstOrFail();
    $missionAttempt = $attemptService->startAttempt($mission, $this->student);
    $missionItem = $mission->activeItems()->firstOrFail();
    $missionAnswer = $attemptService->answerItem($missionAttempt, $missionItem, $this->student, ['choice' => 'C']);

    $image = EducationalGame::where('code', 'image_guess_energy')->firstOrFail();
    $imageAttempt = $attemptService->startAttempt($image, $this->student);
    $imageItem = $image->activeItems()->firstOrFail();
    $imageAnswer = $attemptService->answerItem(
        $imageAttempt,
        $imageItem,
        $this->student,
        ['selected' => 'surya', 'hint_used' => true],
        null,
        true,
    );

    expect((float) $quizAnswer->score)->toBe(0.0)
        ->and($quizAnswer->is_correct)->toBeFalse()
        ->and((float) $missionAnswer->score)->toBe(30.0)
        ->and((float) $imageAnswer->score)->toBe(15.0)
        ->and($imageAnswer->hint_used)->toBeTrue();
});

test('guru can create a game item with uploaded illustration', function () {
    Storage::fake('public');

    $game = EducationalGame::where('code', 'image_guess_energy')->firstOrFail();

    Livewire::actingAs($this->teacher)
        ->test(GameReports::class)
        ->call('selectGame', $game->id)
        ->set('itemPrompt', 'Tebak perangkat energi terbarukan.')
        ->set('itemQuestionText', 'Perangkat apa yang terlihat pada ilustrasi ini?')
        ->set('itemMediaUrl', '')
        ->set('quizOptions', [
            'A' => 'Panel surya',
            'B' => 'Turbin angin',
            'C' => 'Turbin air',
            'D' => '',
        ])
        ->set('quizCorrectKey', 'A')
        ->set('itemExplanation', 'Panel surya mengubah cahaya matahari menjadi energi listrik.')
        ->set('itemScore', 20)
        ->set('itemTimeLimitSeconds', null)
        ->set('itemSortOrder', 99)
        ->set('itemHint', 'Bentuknya berupa bidang datar yang menangkap cahaya.')
        ->set('itemHintPenalty', 5)
        ->set('itemImage', UploadedFile::fake()->image('panel-surya.jpg'))
        ->call('saveItem')
        ->assertHasNoErrors();

    $item = GameItem::where('educational_game_id', $game->id)
        ->where('question_text', 'Perangkat apa yang terlihat pada ilustrasi ini?')
        ->firstOrFail();

    expect($item->media_path)->not->toBeNull()
        ->and(str_starts_with((string) $item->media_path, 'game-items/'))->toBeTrue()
        ->and($item->media_url)->toBe('')
        ->and($item->options)->toBe([
            'A' => 'Panel surya',
            'B' => 'Turbin angin',
            'C' => 'Turbin air',
        ])
        ->and($item->correct_answer)->toBe(['key' => 'A'])
        ->and($item->config)->toBe([
            'hint' => 'Bentuknya berupa bidang datar yang menangkap cahaya.',
            'hint_penalty' => 5,
        ]);

    Storage::disk('public')->assertExists($item->media_path);

    Livewire::actingAs($this->teacher)
        ->test(GameReports::class)
        ->call('selectGame', $game->id)
        ->assertSee(Storage::disk('public')->url($item->media_path), false);
});

test('timed quiz waits for feedback before advancing to the next item', function () {
    $game = EducationalGame::where('code', 'quick_quiz_energy')->firstOrFail();
    $firstItem = $game->activeItems()->firstOrFail();
    $selectedKey = (string) data_get($firstItem->correct_answer, 'key');

    app(GameAttemptService::class)->startAttempt($game, $this->student);

    Livewire::actingAs($this->student)
        ->test(GamePlayPage::class, ['game' => $game->slug])
        ->call('submitQuizAnswer', $selectedKey)
        ->assertSet('awaitingContinue', true)
        ->assertSet('currentItemIndex', 0)
        ->assertSet('selectedAnswerKey', $selectedKey)
        ->call('continueAfterFeedback')
        ->assertSet('awaitingContinue', false)
        ->assertSet('currentItemIndex', 1);
});

test('finished attempts cannot be modified', function () {
    $game = EducationalGame::where('code', 'puzzle_solar_flow')->firstOrFail();
    $item = $game->activeItems()->firstOrFail();
    $attemptService = app(GameAttemptService::class);
    $attempt = $attemptService->startAttempt($game, $this->student);

    $attemptService->answerItem($attempt, $item, $this->student, [
        'order' => ['panel_surya', 'inverter', 'beban_listrik'],
    ]);
    $attemptService->finishAttempt($attempt, $this->student);

    expect(fn () => $attemptService->answerItem($attempt->fresh(), $item, $this->student, [
        'order' => ['inverter', 'panel_surya', 'beban_listrik'],
    ]))->toThrow(ValidationException::class);
});

test('guru can see finished game attempts in report', function () {
    $game = EducationalGame::where('code', 'puzzle_solar_flow')->firstOrFail();
    $item = $game->activeItems()->firstOrFail();
    $attemptService = app(GameAttemptService::class);
    $attempt = $attemptService->startAttempt($game, $this->student);
    $attemptService->answerItem($attempt, $item, $this->student, [
        'order' => ['panel_surya', 'inverter', 'beban_listrik'],
    ]);
    $attemptService->finishAttempt($attempt, $this->student);

    $this->actingAs($this->teacher)
        ->get(route('guru.games.reports'))
        ->assertOk()
        ->assertSee($this->student->name)
        ->assertSee('Puzzle Alur Panel Surya');
});
