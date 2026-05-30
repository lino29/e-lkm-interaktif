<?php

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Models\ProjectRubricScore;
use App\Models\Subject;
use App\Models\User;
use App\Services\Report\ReportExportDataService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('getModuleExportData returns structured export data for a module', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');

    $subject = Subject::create(['name' => 'Export Test', 'code' => 'EXPORT-TEST']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Export Test',
        'slug' => 'modul-export-test',
        'status' => 'published',
    ]);
    $unit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Export',
        'slug' => 'kb-export',
    ]);
    $assessment = Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $unit->id,
        'title' => 'Asesmen Export',
        'type' => 'formative',
        'is_published' => true,
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'total_score' => 80,
        'max_score' => 100,
        'status' => 'tuntas',
        'started_at' => now()->subMinute(),
        'submitted_at' => now(),
    ]);
    Progress::create([
        'user_id' => $student->id,
        'module_id' => $module->id,
        'learning_unit_id' => $unit->id,
        'status' => 'tuntas',
    ]);
    $project = Project::create([
        'module_id' => $module->id,
        'user_id' => $student->id,
        'project_title' => 'Project Export',
        'status' => 'reviewed',
        'score' => 85,
    ]);
    ProjectRubricScore::create([
        'project_id' => $project->id,
        'criterion_key' => 'identifikasi_masalah',
        'criterion' => 'Identifikasi masalah',
        'max_score' => 12,
        'score' => 10,
    ]);
    Discussion::create([
        'learning_unit_id' => $unit->id,
        'user_id' => $student->id,
        'body' => 'Diskusi export test',
        'reviewed_by' => $teacher->id,
        'reviewed_at' => now(),
        'participation_score' => 80,
    ]);

    $result = app(ReportExportDataService::class)->getModuleExportData($module->id);

    expect($result)->toHaveKeys(['module_summary', 'students'])
        ->and($result['module_summary'])->toHaveKeys([
            'module_title', 'total_students', 'total_learning_units',
            'total_assessments', 'total_projects', 'reviewed_projects',
            'average_project_score', 'total_discussions', 'total_progress_records',
        ])
        ->and($result['module_summary']['module_title'])->toBe('Modul Export Test')
        ->and($result['module_summary']['total_students'])->toBe(1)
        ->and($result['module_summary']['reviewed_projects'])->toBe(1)
        ->and($result['module_summary']['average_project_score'])->toBe(85.0);

    $studentRow = $result['students']->first();
    expect($studentRow)->toHaveKeys([
        'name', 'email', 'module_status', 'formative_scores',
        'final_assessment', 'project', 'forum',
    ])
        ->and($studentRow['formative_scores'])->toHaveCount(1)
        ->and($studentRow['formative_scores'][$unit->id]['score'])->toEqual(80)
        ->and($studentRow['project']['score'])->toEqual(85)
        ->and($studentRow['project']['rubric_scores'])->toHaveCount(1)
        ->and($studentRow['forum']['total_discussions'])->toBe(1)
        ->and($studentRow['forum']['average_participation_score'])->toBe(80.0);
});

test('getModuleExportData returns empty students when no progress records exist', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $subject = Subject::create(['name' => 'Empty Export', 'code' => 'EMPTY-EXPORT']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Empty',
        'slug' => 'modul-empty-export',
        'status' => 'published',
    ]);

    $result = app(ReportExportDataService::class)->getModuleExportData($module->id);

    expect($result['students'])->toBeEmpty()
        ->and($result['module_summary']['total_students'])->toBe(0);
});
