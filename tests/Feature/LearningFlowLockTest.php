<?php

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('students can access a later learning unit without completing previous content', function () {
    [$student, , , $secondUnit] = createOpenLearningFixture();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $secondUnit))
        ->assertOk();
});

test('module detail exposes every learning unit without lock messaging', function () {
    [$student, $module, $firstUnit, $secondUnit] = createOpenLearningFixture();

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertOk()
        ->assertSee($firstUnit->title)
        ->assertSee($secondUnit->title)
        ->assertDontSee('Terkunci')
        ->assertDontSee('Selesaikan KB Sebelumnya');

});

test('module quick navigation exposes later learning units without lock messaging', function () {
    [$student, , , $secondUnit] = createOpenLearningFixture();

    $this->actingAs($student)
        ->get(route('murid.modules'))
        ->assertOk()
        ->assertSee($secondUnit->title)
        ->assertDontSee('Terkunci');

});

test('students can access assessments without completing earlier content', function () {
    [$student, , , $secondUnit] = createOpenLearningFixture();
    $secondAssessment = $secondUnit->assessments()->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.assessments.show', $secondAssessment))
        ->assertOk();
});

/**
 * @return array{0: User, 1: Module, 2: LearningUnit, 3: LearningUnit, 4: Activity, 5: Assessment}
 */
function createOpenLearningFixture(): array
{
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Lock', 'code' => 'IPAS-LOCK']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Lock',
        'slug' => 'modul-lock',
        'status' => 'published',
    ]);
    $firstUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB1 Lock',
        'slug' => 'kb1-lock',
        'order' => 1,
    ]);
    $secondUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB2 Lock',
        'slug' => 'kb2-lock',
        'order' => 2,
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $firstUnit->id,
        'title' => 'Ayo Mengamati Lock',
        'phase' => 'ayo_mengamati',
        'is_required' => true,
    ]);
    Activity::create([
        'learning_unit_id' => $secondUnit->id,
        'title' => 'Ayo Mengamati Lock 2',
        'phase' => 'ayo_mengamati',
        'is_required' => true,
    ]);
    $assessment = Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $firstUnit->id,
        'title' => 'Asesmen Lock',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ]);
    Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Pilih energi terbarukan.',
        'question_type' => 'multiple_choice',
        'options' => ['A' => 'Matahari', 'B' => 'Batu bara'],
        'correct_answer' => ['A'],
        'weight' => 10,
    ]);
    $secondAssessment = Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $secondUnit->id,
        'title' => 'Asesmen Lock 2',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ]);
    Question::create([
        'assessment_id' => $secondAssessment->id,
        'question_text' => 'Pilih energi terbarukan kedua.',
        'question_type' => 'multiple_choice',
        'options' => ['A' => 'Angin', 'B' => 'Batu bara'],
        'correct_answer' => ['A'],
        'weight' => 10,
    ]);

    return [$student, $module, $firstUnit, $secondUnit, $activity, $assessment];
}
