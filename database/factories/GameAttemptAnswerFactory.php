<?php

namespace Database\Factories;

use App\Models\GameAttempt;
use App\Models\GameAttemptAnswer;
use App\Models\GameItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameAttemptAnswer>
 */
class GameAttemptAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_attempt_id' => GameAttempt::factory(),
            'game_item_id' => GameItem::factory(),
            'answer' => ['selected' => 'A'],
            'is_correct' => true,
            'score' => 10,
            'time_spent_seconds' => 5,
            'hint_used' => false,
            'feedback' => 'Jawaban benar.',
            'answered_at' => now(),
            'metadata' => [],
        ];
    }
}
