<?php

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Project;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can manage domain records', function () {
    [$admin, $teacher, $student, $module, $learningUnit, $activity, $assessment, $project, $studentAnswer] = createPolicyFixture($this);

    expect($admin->can('update', $module))->toBeTrue()
        ->and($admin->can('update', $learningUnit))->toBeTrue()
        ->and($admin->can('update', $activity))->toBeTrue()
        ->and($admin->can('update', $assessment))->toBeTrue()
        ->and($admin->can('update', $project))->toBeTrue()
        ->and($admin->can('view', $studentAnswer))->toBeTrue()
        ->and($teacher->can('update', $module))->toBeTrue()
        ->and($student->can('view', $module))->toBeTrue();
});

test('guru can only manage modules and descendants they own', function () {
    [, $teacher, , $module, $learningUnit, $activity, $assessment, $project, $studentAnswer] = createPolicyFixture($this);
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');

    expect($teacher->can('update', $module))->toBeTrue()
        ->and($teacher->can('update', $learningUnit))->toBeTrue()
        ->and($teacher->can('update', $activity))->toBeTrue()
        ->and($teacher->can('update', $assessment))->toBeTrue()
        ->and($teacher->can('update', $project))->toBeTrue()
        ->and($teacher->can('view', $studentAnswer))->toBeTrue()
        ->and($otherTeacher->can('update', $module))->toBeFalse()
        ->and($otherTeacher->can('update', $learningUnit))->toBeFalse()
        ->and($otherTeacher->can('update', $activity))->toBeFalse()
        ->and($otherTeacher->can('update', $assessment))->toBeFalse()
        ->and($otherTeacher->can('update', $project))->toBeFalse()
        ->and($otherTeacher->can('view', $studentAnswer))->toBeFalse();
});

test('murid can only view published learning data and their own records', function () {
    [, , $student, $module, , , $assessment, $project, $studentAnswer] = createPolicyFixture($this);
    $otherStudent = User::factory()->create();
    $otherStudent->assignRole('murid');

    $draftModule = Module::create([
        'subject_id' => $module->subject_id,
        'created_by' => $module->created_by,
        'title' => 'Draft Modul',
        'slug' => 'draft-modul-policy',
        'status' => 'draft',
    ]);
    $draftAssessment = Assessment::create([
        'module_id' => $module->id,
        'title' => 'Draft Assessment',
        'is_published' => false,
    ]);

    expect($student->can('view', $module))->toBeTrue()
        ->and($student->can('view', $assessment))->toBeTrue()
        ->and($student->can('view', $draftModule))->toBeFalse()
        ->and($student->can('view', $draftAssessment))->toBeFalse()
        ->and($student->can('view', $project))->toBeTrue()
        ->and($student->can('view', $studentAnswer))->toBeTrue()
        ->and($otherStudent->can('view', $project))->toBeFalse()
        ->and($otherStudent->can('view', $studentAnswer))->toBeFalse();
});

/**
 * @return array{0: User, 1: User, 2: User, 3: Module, 4: LearningUnit, 5: Activity, 6: Assessment, 7: Project, 8: StudentAnswer}
 */
function createPolicyFixture(object $testCase): array
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Policy', 'code' => 'IPAS-POLICY']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Policy',
        'slug' => 'modul-policy',
        'status' => 'published',
    ]);
    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Policy',
        'slug' => 'kb-policy',
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Ayo Policy',
        'phase' => 'ayo_mengamati',
    ]);
    $assessment = Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Assessment Policy',
        'is_published' => true,
    ]);
    $question = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Policy question',
        'question_type' => 'multiple_choice',
        'correct_answer' => ['A'],
        'weight' => 10,
    ]);
    $attempt = AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'status' => 'tuntas',
        'submitted_at' => now(),
    ]);
    $studentAnswer = StudentAnswer::create([
        'assessment_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'student_id' => $student->id,
        'score' => 10,
    ]);
    $project = Project::create([
        'module_id' => $module->id,
        'user_id' => $student->id,
        'project_title' => 'Project Policy',
        'status' => 'submitted',
    ]);

    return [$admin, $teacher, $student, $module, $learningUnit, $activity, $assessment, $project, $studentAnswer];
}
