<?php

namespace Database\Factories;

use App\Models\LearningUnit;
use App\Models\LearningUnitGrade;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningUnitGrade>
 */
class LearningUnitGradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_unit_id' => function (): int {
                $teacher = User::factory()->create();
                $subject = Subject::create([
                    'name' => fake()->unique()->words(2, true),
                    'code' => fake()->unique()->bothify('SUB-####'),
                ]);
                $module = Module::create([
                    'subject_id' => $subject->id,
                    'created_by' => $teacher->id,
                    'title' => fake()->sentence(3),
                    'slug' => fake()->unique()->slug(),
                    'status' => 'published',
                ]);

                return LearningUnit::create([
                    'module_id' => $module->id,
                    'title' => fake()->sentence(3),
                    'slug' => fake()->unique()->slug(),
                    'order' => fake()->numberBetween(1, 5),
                ])->id;
            },
            'student_id' => User::factory(),
            'reviewed_by' => null,
            'score' => null,
            'feedback' => null,
            'reviewed_at' => null,
        ];
    }

    public function reviewed(?User $teacher = null): static
    {
        return $this->state(fn (): array => [
            'reviewed_by' => $teacher?->id ?? User::factory(),
            'score' => fake()->randomFloat(2, 0, 20),
            'feedback' => fake()->sentence(),
            'reviewed_at' => now(),
        ]);
    }
}
