<?php

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\Learning\ProgressService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('learning unit one is open and next unit is locked until previous unit is complete', function () {
    [$student, $module, $firstUnit, $secondUnit, $activity, $assessment] = createLearningLockFixture();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $firstUnit))
        ->assertOk();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $secondUnit))
        ->assertForbidden();

    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'answer_text' => 'Jawaban aktivitas lengkap.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'total_score' => 10,
        'max_score' => 10,
        'status' => 'tuntas',
        'started_at' => now()->subMinute(),
        'submitted_at' => now(),
    ]);

    app(ProgressService::class)->refreshLearningUnitProgress($student, $firstUnit);

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $secondUnit))
        ->assertOk();

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertOk()
        ->assertSee('50%')
        ->assertSee('Tuntas');

    $this->actingAs($student)
        ->get(route('murid.dashboard'))
        ->assertOk()
        ->assertSee('Progress Rata-rata')
        ->assertSee('50%');
});

test('failed formative assessment marks the learning unit as remedial and keeps next unit locked', function () {
    [$student, , $firstUnit, $secondUnit, $activity, $assessment] = createLearningLockFixture();

    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'answer_text' => 'Jawaban aktivitas lengkap.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'total_score' => 0,
        'max_score' => 10,
        'status' => 'remedial',
        'started_at' => now()->subMinute(),
        'submitted_at' => now(),
    ]);

    $progress = app(ProgressService::class)->refreshLearningUnitProgress($student, $firstUnit);

    expect($progress->status)->toBe('remedial')
        ->and(Progress::where('user_id', $student->id)->where('learning_unit_id', $firstUnit->id)->first()?->status)->toBe('remedial');

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $secondUnit))
        ->assertForbidden();
});

/**
 * @return array{0: User, 1: Module, 2: LearningUnit, 3: LearningUnit, 4: Activity, 5: Assessment}
 */
function createLearningLockFixture(): array
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
