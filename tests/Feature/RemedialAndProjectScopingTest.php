<?php

use App\Livewire\Guru\ManageProjects;
use App\Livewire\Guru\Reports;
use App\Livewire\Murid\MyProject;
use App\Livewire\Murid\RemedialPage;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Project;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('murid sees only their own remedial list with remaining attempts', function () {
    [$teacher, $student, $otherStudent, $module, $assessment] = createRemedialProjectFixture();

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'total_score' => 40,
        'max_score' => 100,
        'status' => 'remedial',
        'submitted_at' => now(),
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $otherStudent->id,
        'attempt_number' => 1,
        'total_score' => 20,
        'max_score' => 100,
        'status' => 'remedial',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($student)
        ->test(RemedialPage::class)
        ->assertSee($assessment->title)
        ->assertSee('Sisa percobaan: 1')
        ->assertDontSee($otherStudent->name)
        ->assertSee('Ulangi Asesmen');

    expect($teacher->can('view', $module))->toBeTrue();
});

test('murid cannot retry remedial when attempts are exhausted', function () {
    [, $student, , , $assessment] = createRemedialProjectFixture(['max_attempts' => 1]);

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'total_score' => 40,
        'max_score' => 100,
        'status' => 'remedial',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($student)
        ->test(RemedialPage::class)
        ->assertSee('Sisa percobaan: 0')
        ->assertSee('Batas percobaan sudah habis')
        ->assertDontSee('Ulangi Asesmen');
});

test('murid can submit complete project data only for published modules', function () {
    [, $student, , $module] = createRemedialProjectFixture();

    Livewire::actingAs($student)
        ->test(MyProject::class)
        ->set('module_id', $module->id)
        ->set('project_title', 'Aksi Hemat Energi')
        ->set('problem', 'Pemakaian listrik kelas terlalu tinggi.')
        ->set('objective', 'Mengurangi konsumsi listrik.')
        ->set('tools_materials', 'Kertas, alat ukur, lampu LED.')
        ->set('procedure', 'Observasi, ukur, rancang aksi.')
        ->set('collected_data', 'Data pemakaian listrik harian.')
        ->set('expected_result', 'Penggunaan listrik menurun.')
        ->set('conclusion', 'Aksi sederhana dapat membantu penghematan.')
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::where('user_id', $student->id)->firstOrFail();

    expect($project->status)->toBe('submitted')
        ->and($project->tools_materials)->toContain('Kertas')
        ->and($project->expected_result)->toContain('menurun');
});

test('guru only sees and reviews projects from their modules', function () {
    [$teacher, $student, $otherStudent, $module] = createRemedialProjectFixture();
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');
    $otherSubject = Subject::create(['name' => 'IPAS Other Project', 'code' => 'IPAS-OTHER-PROJECT']);
    $otherModule = Module::create([
        'subject_id' => $otherSubject->id,
        'created_by' => $otherTeacher->id,
        'title' => 'Modul Guru Lain',
        'slug' => 'modul-guru-lain-project',
        'status' => 'published',
    ]);

    $ownProject = Project::create([
        'module_id' => $module->id,
        'user_id' => $student->id,
        'project_title' => 'Project Milik Guru',
        'status' => 'submitted',
    ]);
    Project::create([
        'module_id' => $otherModule->id,
        'user_id' => $otherStudent->id,
        'project_title' => 'Project Guru Lain',
        'status' => 'submitted',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageProjects::class)
        ->assertSee('Project Milik Guru')
        ->assertDontSee('Project Guru Lain')
        ->call('review', $ownProject->id)
        ->set('rubricScores.identifikasi_masalah', 11)
        ->set('rubricScores.kesesuaian_solusi', 11)
        ->set('rubricScores.kelengkapan_rancangan', 11)
        ->set('rubricScores.data_pengamatan', 11)
        ->set('rubricScores.keselamatan_kerja', 11)
        ->set('rubricScores.kreativitas', 11)
        ->set('rubricScores.kelayakan', 11)
        ->set('rubricScores.komunikasi_hasil', 11)
        ->set('feedback', 'Proyek sudah baik.')
        ->call('saveReview')
        ->assertHasNoErrors();

    expect($ownProject->fresh())
        ->status->toBe('reviewed')
        ->score->toBe('88.00')
        ->feedback->toBe('Proyek sudah baik.')
        ->rubricScores->toHaveCount(8);
});

test('guru reports are scoped to their own modules', function () {
    [$teacher, $student, $otherStudent, $module, $assessment] = createRemedialProjectFixture();
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');
    $otherSubject = Subject::create(['name' => 'IPAS Other Report', 'code' => 'IPAS-OTHER-REPORT']);
    $otherModule = Module::create([
        'subject_id' => $otherSubject->id,
        'created_by' => $otherTeacher->id,
        'title' => 'Modul Report Lain',
        'slug' => 'modul-report-lain',
        'status' => 'published',
    ]);
    $learningUnit = $module->learningUnits()->firstOrFail();
    $otherLearningUnit = LearningUnit::create([
        'module_id' => $otherModule->id,
        'title' => 'KB Report Lain',
        'slug' => 'kb-report-lain',
    ]);
    $otherAssessment = Assessment::create([
        'module_id' => $otherModule->id,
        'title' => 'Assessment Lain',
        'is_published' => true,
    ]);
    $discussion = Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'body' => 'Diskusi milik modul guru',
        'reviewed_by' => $teacher->id,
        'reviewed_at' => now(),
        'participation_score' => 80,
        'participation_feedback' => 'Refleksi sudah baik.',
    ]);
    Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'parent_id' => $discussion->id,
        'body' => 'Reply murid pada modul guru',
    ]);
    Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $teacher->id,
        'parent_id' => $discussion->id,
        'body' => 'Feedback guru pada diskusi.',
    ]);
    Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'body' => 'Diskusi belum direspons',
    ]);
    Discussion::create([
        'learning_unit_id' => $otherLearningUnit->id,
        'user_id' => $otherStudent->id,
        'body' => 'Diskusi modul lain',
    ]);
    Project::create([
        'module_id' => $module->id,
        'user_id' => $student->id,
        'project_title' => 'Project Laporan Submitted',
        'status' => 'submitted',
    ]);
    Project::create([
        'module_id' => $module->id,
        'user_id' => $student->id,
        'project_title' => 'Project Laporan Reviewed',
        'status' => 'reviewed',
        'score' => 82,
    ]);
    Project::create([
        'module_id' => $otherModule->id,
        'user_id' => $otherStudent->id,
        'project_title' => 'Project Modul Lain',
        'status' => 'reviewed',
        'score' => 10,
    ]);

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'status' => 'remedial',
        'submitted_at' => now(),
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $otherAssessment->id,
        'student_id' => $otherStudent->id,
        'attempt_number' => 1,
        'status' => 'remedial',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($teacher)
        ->test(Reports::class)
        ->assertSee($assessment->title)
        ->assertSee('Diskusi milik modul guru')
        ->assertSee('Direspons guru')
        ->assertSee('Belum direspons guru')
        ->assertSee('3 diskusi dan reply')
        ->assertSee('rata-rata skor 80')
        ->assertSee('Rata-rata Nilai Proyek')
        ->assertSee('82')
        ->assertSee('Project Laporan Reviewed')
        ->assertDontSee('Assessment Lain')
        ->assertDontSee('Diskusi modul lain')
        ->assertDontSee('Project Modul Lain');
});

/**
 * @return array{0: User, 1: User, 2: User, 3: Module, 4: Assessment}
 */
function createRemedialProjectFixture(array $assessmentOverrides = []): array
{
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $otherStudent = User::factory()->create();
    $otherStudent->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Remedial Project', 'code' => 'IPAS-REMEDIAL-PROJECT']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Remedial Project',
        'slug' => 'modul-remedial-project',
        'status' => 'published',
    ]);
    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Remedial Project',
        'slug' => 'kb-remedial-project',
    ]);
    $assessment = Assessment::create(array_merge([
        'module_id' => $module->id,
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Assessment Remedial Project',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ], $assessmentOverrides));

    return [$teacher, $student, $otherStudent, $module, $assessment];
}
