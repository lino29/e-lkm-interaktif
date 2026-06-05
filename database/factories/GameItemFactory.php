<?php

namespace Database\Factories;

use App\Models\EducationalGame;
use App\Models\GameItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameItem>
 */
class GameItemFactory extends Factory
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
            'item_type' => 'question',
            'prompt' => fake()->sentence(),
            'question_text' => fake()->sentence(),
            'options' => [
                'A' => 'Pilihan A',
                'B' => 'Pilihan B',
            ],
            'correct_answer' => ['key' => 'A'],
            'explanation' => fake()->sentence(),
            'score' => 10,
            'time_limit_seconds' => 10,
            'sort_order' => fake()->numberBetween(1, 10),
            'config' => [],
            'is_active' => true,
        ];
    }
}
