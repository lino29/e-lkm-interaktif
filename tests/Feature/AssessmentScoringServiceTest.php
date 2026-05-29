<?php

use App\Models\Assessment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Rubric;
use App\Models\Subject;
use App\Models\User;
use App\Services\Assessment\AssessmentScoringService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Energi',
        'slug' => 'energi',
    ]);

    $this->assessment = Assessment::create([
        'module_id' => $module->id,
        'title' => 'Asesmen',
    ]);
});

test('scores multiple choice answers', function () {
    $question = Question::create([
        'assessment_id' => $this->assessment->id,
        'question_text' => 'Pilih energi terbarukan',
        'question_type' => 'multiple_choice',
        'correct_answer' => ['B'],
        'weight' => 10,
    ]);

    $service = app(AssessmentScoringService::class);

    expect($service->scoreQuestion($question, 'B')['score'])->toBe(10.0)
        ->and($service->scoreQuestion($question, 'A')['score'])->toBe(0.0);
});

test('scores complex choice with penalty', function () {
    $question = Question::create([
        'assessment_id' => $this->assessment->id,
        'question_text' => 'Pilih semua energi terbarukan',
        'question_type' => 'complex_multiple_choice',
        'correct_answer' => ['A', 'C', 'D'],
        'weight' => 9,
    ]);

    $score = app(AssessmentScoringService::class)->scoreQuestion($question, ['A', 'C', 'B'])['score'];

    expect($score)->toBe(3.0);
});

test('scores matching short answer and essay', function () {
    $matching = Question::create([
        'assessment_id' => $this->assessment->id,
        'question_text' => 'Jodohkan',
        'question_type' => 'matching',
        'correct_answer' => ['surya' => 'matahari', 'angin' => 'turbin'],
        'weight' => 10,
    ]);

    $short = Question::create([
        'assessment_id' => $this->assessment->id,
        'question_text' => 'Sumber energi surya',
        'question_type' => 'short_answer',
        'weight' => 10,
    ]);
    QuestionKeyword::create(['question_id' => $short->id, 'keyword' => 'matahari', 'weight' => 1]);

    $essay = Question::create([
        'assessment_id' => $this->assessment->id,
        'question_text' => 'Jelaskan surya',
        'question_type' => 'essay',
        'reference_answer' => 'Energi surya berasal dari matahari dan tidak memakai bahan bakar fosil.',
        'weight' => 10,
    ]);
    QuestionKeyword::create(['question_id' => $essay->id, 'keyword' => 'matahari', 'weight' => 1]);
    Rubric::create(['question_id' => $essay->id, 'criterion' => 'Konsep', 'score' => 80]);

    $service = app(AssessmentScoringService::class);

    expect($service->scoreQuestion($matching, ['surya' => 'matahari', 'angin' => 'angin'])['score'])->toBe(5.0)
        ->and($service->scoreQuestion($short, 'Energi dari matahari')['score'])->toBe(10.0)
        ->and($service->scoreQuestion($essay, 'Energi surya berasal dari matahari tanpa bahan bakar fosil')['score'])->toBeGreaterThan(6.0);
});
