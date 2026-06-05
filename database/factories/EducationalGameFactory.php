<?php

namespace Database\Factories;

use App\Models\EducationalGame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationalGame>
 */
class EducationalGameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->sentence(3),
            'type' => 'timed_quiz',
            'icon' => 'GAME',
            'description' => fake()->sentence(),
            'config' => [
                'allow_replay' => true,
            ],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
