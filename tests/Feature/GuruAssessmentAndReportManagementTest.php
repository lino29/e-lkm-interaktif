<?php

use App\Livewire\Guru\ManageAssessments;
use App\Livewire\Guru\ManageQuestions;
use App\Livewire\Guru\ManageRubrics;
use App\Livewire\Guru\Reports;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use App\Models\Rubric;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('guru shell links assessment and report menus to real pages', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $this->actingAs($teacher)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee(route('guru.assessments'), false)
        ->assertSee(route('guru.reports'), false);
});

test('guru can create edit publish and delete assessments', function () {
    [$teacher, $module, $unit] = createTeacherAssessmentFixture();

    Livewire::actingAs($teacher)
        ->test(ManageAssessments::class)
        ->assertSee('Kelola Soal')
        ->set('module_id', $module->id)
        ->set('learning_unit_id', $unit->id)
        ->set('title', 'Asesmen Energi Surya')
        ->set('type', 'formative')
        ->set('description', 'Mengukur konsep energi surya.')
        ->set('kktp', 78)
        ->set('max_attempts', 3)
        ->set('order', 2)
        ->call('save')
        ->assertHasNoErrors();

    $assessment = Assessment::where('module_id', $module->id)->firstOrFail();

    expect($assessment)
        ->title->toBe('Asesmen Energi Surya')
        ->learning_unit_id->toBe($unit->id)
        ->is_published->toBeFalse();

    Livewire::actingAs($teacher)
        ->test(ManageAssessments::class)
        ->call('edit', $assessment->id)
        ->assertSet('editingAssessmentId', $assessment->id)
        ->set('title', 'Asesmen Energi Surya Revisi')
        ->set('is_published', true)
        ->call('save')
        ->assertHasNoErrors()
        ->call('togglePublish', $assessment->id)
        ->assertHasNoErrors()
        ->call('delete', $assessment->id)
        ->assertHasNoErrors();

    expect($assessment->fresh())->toBeNull();
});

test('guru can manage short answer questions and rubrics', function () {
    [$teacher, $module, $unit] = createTeacherAssessmentFixture();
    $assessment = createAssessmentForTeacher($module, $unit);

    Livewire::actingAs($teacher)
        ->test(ManageQuestions::class)
        ->assertSee('Tambah Soal')
        ->set('assessment_id', $assessment->id)
        ->set('question_type', 'short_answer')
        ->set('question_text', 'Sebutkan sumber energi terbarukan.')
        ->set('reference_answer', 'Matahari, angin, air, biomassa, dan panas bumi.')
        ->set('keywords', 'matahari, angin, air')
        ->set('weight', 12)
        ->call('save')
        ->assertHasNoErrors();

    $question = Question::where('assessment_id', $assessment->id)->firstOrFail();

    expect($question)
        ->question_type->toBe('short_answer')
        ->weight->toEqual(12)
        ->and($question->keywords()->pluck('keyword')->all())->toContain('matahari', 'angin', 'air');

    Livewire::actingAs($teacher)
        ->test(ManageQuestions::class)
        ->call('edit', $question->id)
        ->assertSet('editingQuestionId', $question->id)
        ->set('question_text', 'Jelaskan dua sumber energi terbarukan.')
        ->call('save')
        ->assertHasNoErrors();

    expect($question->fresh()->question_text)->toBe('Jelaskan dua sumber energi terbarukan.');

    Livewire::actingAs($teacher)
        ->test(ManageRubrics::class)
        ->set('question_id', $question->id)
        ->set('criterion', 'Ketepatan konsep')
        ->set('level', 'Baik')
        ->set('description', 'Jawaban memuat contoh dan alasan.')
        ->set('score', 80)
        ->call('save')
        ->assertHasNoErrors();

    $rubric = Rubric::where('question_id', $question->id)->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageRubrics::class)
        ->call('edit', $rubric->id)
        ->assertSet('editingRubricId', $rubric->id)
        ->set('score', 90)
        ->call('save')
        ->assertHasNoErrors()
        ->call('delete', $rubric->id)
        ->assertHasNoErrors();

    expect($rubric->fresh())->toBeNull();
});

test('guru reports can filter attempts by status and student search', function () {
    [$teacher, $module, $unit] = createTeacherAssessmentFixture();
    $assessment = createAssessmentForTeacher($module, $unit);

    $tuntasStudent = User::factory()->create(['name' => 'Murid Tuntas']);
    $tuntasStudent->assignRole('murid');
    $remedialStudent = User::factory()->create(['name' => 'Murid Remedial']);
    $remedialStudent->assignRole('murid');

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $tuntasStudent->id,
        'attempt_number' => 1,
        'total_score' => 90,
        'max_score' => 100,
        'status' => 'tuntas',
        'started_at' => now()->subMinutes(5),
        'submitted_at' => now(),
    ]);

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $remedialStudent->id,
        'attempt_number' => 1,
        'total_score' => 50,
        'max_score' => 100,
        'status' => 'remedial',
        'started_at' => now()->subMinutes(5),
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($teacher)
        ->test(Reports::class)
        ->assertSee('Murid Tuntas')
        ->assertSee('Murid Remedial')
        ->set('attempt_status', 'remedial')
        ->set('search', 'Remedial')
        ->assertSee('Murid Remedial')
        ->assertDontSee('Murid Tuntas');
});

/**
 * @return array{0: User, 1: Module, 2: LearningUnit}
 */
function createTeacherAssessmentFixture(): array
{
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $subject = Subject::create([
        'name' => 'Projek IPAS',
        'code' => 'IPAS-ASSESSMENT',
    ]);

    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Energi',
        'slug' => 'modul-energi',
        'status' => 'published',
    ]);

    $unit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Energi Surya',
        'slug' => 'kb-energi-surya',
    ]);

    return [$teacher, $module, $unit];
}

function createAssessmentForTeacher(Module $module, LearningUnit $unit): Assessment
{
    return Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $unit->id,
        'title' => 'Asesmen Formatif Energi',
        'type' => 'formative',
        'description' => 'Asesmen formatif.',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
        'order' => 1,
    ]);
}
