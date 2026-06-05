<?php

namespace Database\Factories;

use App\Models\EducationalGame;
use App\Models\GameAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameAttempt>
 */
class GameAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'educational_game_id' => EducationalGame::factory(),
            'user_id' => User::factory(),
            'status' => 'in_progress',
            'attempt_number' => 1,
            'score' => 0,
            'max_score' => 0,
            'duration_seconds' => 0,
            'started_at' => now(),
            'metadata' => [],
        ];
    }
}
