<?php

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\ClassRoom;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Models\ProjectRubricScore;
use App\Models\Subject;
use App\Models\User;
use App\Services\Report\ReportSummaryService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin report summary includes core system activity counts', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    ClassRoom::create(['name' => 'X TKJ 1', 'code' => 'X-TKJ-1']);
    $subject = Subject::create(['name' => 'IPAS Report', 'code' => 'IPAS-REPORT']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Report',
        'slug' => 'modul-report-summary',
        'status' => 'published',
    ]);
    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Report',
        'slug' => 'kb-report',
    ]);
    Activity::create(['learning_unit_id' => $learningUnit->id, 'title' => 'Ayo Report', 'phase' => 'ayo_mengamati']);
    Assessment::create(['module_id' => $module->id, 'learning_unit_id' => $learningUnit->id, 'title' => 'Assessment Report', 'is_published' => true]);
    Discussion::create(['learning_unit_id' => $learningUnit->id, 'user_id' => $student->id, 'body' => 'Diskusi report', 'reviewed_by' => $teacher->id, 'reviewed_at' => now(), 'participation_score' => 75]);
    Progress::create(['user_id' => $student->id, 'module_id' => $module->id, 'learning_unit_id' => $learningUnit->id, 'status' => 'sedang_dikerjakan']);
    Project::create(['module_id' => $module->id, 'user_id' => $student->id, 'project_title' => 'Project Report', 'status' => 'submitted']);
    $reviewedProject = Project::create(['module_id' => $module->id, 'user_id' => $student->id, 'project_title' => 'Project Reviewed', 'status' => 'reviewed', 'score' => 90]);
    ProjectRubricScore::create([
        'project_id' => $reviewedProject->id,
        'criterion_key' => 'identifikasi_masalah',
        'criterion' => 'Identifikasi masalah',
        'max_score' => 12,
        'score' => 10,
    ]);

    $stats = app(ReportSummaryService::class)->systemSummary();

    expect($stats)->toHaveKeys(['users_total', 'users_by_role', 'classes', 'modules', 'learning_units', 'assessments', 'activities', 'progress_records', 'discussions', 'discussions_by_type', 'average_participation_score', 'projects', 'projects_by_status', 'project_rubric_scores', 'reviewed_project_average_score'])
        ->and($stats['users_by_role'])->toHaveKeys(['admin', 'guru', 'murid'])
        ->and($stats['discussions_by_type'])->toHaveKey('threads')
        ->and($stats['discussions_by_type'])->toHaveKey('reviewed')
        ->and($stats['average_participation_score'])->toBe(75.0)
        ->and($stats['projects_by_status'])->toHaveKeys(['submitted', 'reviewed'])
        ->and($stats['project_rubric_scores'])->toBe(1)
        ->and($stats['reviewed_project_average_score'])->toBe(90.0);

    $this->actingAs($admin)
        ->get(route('admin.reports'))
        ->assertOk()
        ->assertSee('Assessments')
        ->assertSee('Discussions');
});
