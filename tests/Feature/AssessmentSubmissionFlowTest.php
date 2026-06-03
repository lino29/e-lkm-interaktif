<?php

use App\Livewire\Murid\AssessmentPage;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Rubric;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\User;
use App\Services\Assessment\QuestionGroupService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('guru');
    $this->student = User::factory()->create();
    $this->student->assignRole('murid');
    $this->subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS-ASSESS']);
    $this->module = Module::create([
        'subject_id' => $this->subject->id,
        'created_by' => $this->teacher->id,
        'title' => 'Modul Assessment',
        'slug' => 'modul-assessment',
        'status' => 'published',
    ]);
    $this->learningUnit = LearningUnit::create([
        'module_id' => $this->module->id,
        'title' => 'Kegiatan Assessment',
        'slug' => 'kegiatan-assessment',
    ]);
});

test('route list does not fail', function () {
    expect(Artisan::call('route:list', ['--except-vendor' => true]))->toBe(0);
});

test('opening a published assessment creates an active attempt', function () {
    [$assessment] = createAssessmentFlowFixture($this);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->assertOk();

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)
        ->where('student_id', $this->student->id)
        ->firstOrFail();

    expect($attempt->status)->toBe('sedang_dikerjakan')
        ->and($attempt->submitted_at)->toBeNull()
        ->and($attempt->attempt_number)->toBe(1);
});

test('assessment submit stores answers and total score', function () {
    [$assessment, $question] = createAssessmentFlowFixture($this);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'B')
        ->call('submit')
        ->assertHasNoErrors();

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)->firstOrFail();
    $answer = StudentAnswer::where('assessment_attempt_id', $attempt->id)->firstOrFail();

    expect((float) $attempt->total_score)->toBe(10.0)
        ->and((float) $attempt->max_score)->toBe(10.0)
        ->and($attempt->status)->toBe('tuntas')
        ->and($attempt->submitted_at)->not->toBeNull()
        ->and((float) $answer->score)->toBe(10.0);
});

test('submitted assessment result and review pages render from route parameters', function () {
    [$assessment, $question] = createAssessmentFlowFixture($this);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'B')
        ->call('submit')
        ->assertHasNoErrors();

    $this->actingAs($this->student)
        ->get(route('murid.assessments.result', $assessment->id))
        ->assertOk()
        ->assertSee('Hasil Asesmen')
        ->assertSee($assessment->title);

    $this->actingAs($this->student)
        ->get(route('murid.assessments.review', $assessment->id))
        ->assertOk()
        ->assertSee('Review Hasil')
        ->assertSee($question->question_text);
});

test('failed assessment updates progress as remedial', function () {
    [$assessment, $question] = createAssessmentFlowFixture($this);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'A')
        ->call('submit')
        ->assertHasNoErrors();

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)->firstOrFail();
    $progress = Progress::where('assessment_id', $assessment->id)
        ->where('user_id', $this->student->id)
        ->firstOrFail();

    expect($attempt->status)->toBe('remedial')
        ->and($progress->status)->toBe('remedial')
        ->and($progress->completed_at)->toBeNull();
});

test('submitted attempt cannot be submitted again', function () {
    [$assessment, $question] = createAssessmentFlowFixture($this);

    $component = Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'B')
        ->call('submit')
        ->assertHasNoErrors();

    $component
        ->set("answers.{$question->id}", 'A')
        ->call('submit')
        ->assertHasNoErrors();

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)->firstOrFail();

    expect(AssessmentAttempt::where('assessment_id', $assessment->id)->count())->toBe(1)
        ->and(StudentAnswer::where('assessment_attempt_id', $attempt->id)->count())->toBe(1)
        ->and((float) $attempt->fresh()->total_score)->toBe(10.0);
});

test('max attempts is enforced', function () {
    [$assessment, $question] = createAssessmentFlowFixture($this, ['max_attempts' => 1]);

    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'attempt_number' => 1,
        'total_score' => 0,
        'max_score' => 10,
        'status' => 'remedial',
        'started_at' => now()->subMinutes(5),
        'submitted_at' => now()->subMinute(),
    ]);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'B')
        ->call('submit')
        ->assertHasNoErrors();

    expect(AssessmentAttempt::where('assessment_id', $assessment->id)->count())->toBe(1)
        ->and(StudentAnswer::count())->toBe(0);
});

test('essay scoring stores rubric keyword and similarity scores', function () {
    [$assessment, $question] = createAssessmentFlowFixture(
        $this,
        [],
        [
            'question_type' => 'essay',
            'question_text' => 'Jelaskan energi surya.',
            'reference_answer' => 'Energi surya berasal dari matahari dan dapat diubah menjadi listrik.',
            'correct_answer' => null,
        ],
    );
    QuestionKeyword::create(['question_id' => $question->id, 'keyword' => 'matahari', 'weight' => 1]);
    Rubric::create(['question_id' => $question->id, 'criterion' => 'Konsep', 'score' => 80]);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$question->id}", 'Energi surya berasal dari matahari dan menghasilkan listrik.')
        ->call('submit')
        ->assertHasNoErrors();

    $answer = StudentAnswer::firstOrFail();

    expect($answer->rubric_score)->not->toBeNull()
        ->and($answer->keyword_score)->not->toBeNull()
        ->and($answer->similarity_score)->not->toBeNull()
        ->and((float) AssessmentAttempt::firstOrFail()->total_score)->toBeGreaterThan(0.0);
});

test('complex and matching answers can be submitted as arrays', function () {
    $assessment = Assessment::create([
        'module_id' => $this->module->id,
        'learning_unit_id' => $this->learningUnit->id,
        'title' => 'Asesmen Struktur',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ]);
    $complex = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Pilih energi terbarukan.',
        'question_type' => 'complex_multiple_choice',
        'options' => ['A' => 'Surya', 'B' => 'Angin', 'C' => 'Batu bara'],
        'correct_answer' => ['A', 'B'],
        'weight' => 10,
    ]);
    $matching = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Jodohkan sumber energi.',
        'question_type' => 'matching',
        'options' => ['surya' => 'Matahari', 'angin' => 'Turbin'],
        'correct_answer' => ['surya' => 'Matahari', 'angin' => 'Turbin'],
        'weight' => 10,
    ]);

    Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->set("answers.{$complex->id}", ['A', 'B'])
        ->set("answers.{$matching->id}", ['surya' => 'Matahari', 'angin' => 'Turbin'])
        ->call('submit')
        ->assertHasNoErrors();

    expect((float) AssessmentAttempt::where('assessment_id', $assessment->id)->firstOrFail()->total_score)->toBe(20.0)
        ->and(StudentAnswer::where('question_id', $complex->id)->firstOrFail()->answer_json)->toBe(['A', 'B'])
        ->and(StudentAnswer::where('question_id', $matching->id)->firstOrFail()->answer_json)->toBe(['surya' => 'Matahari', 'angin' => 'Turbin']);
});

test('assessment page saves and navigates question groups one at a time', function () {
    $assessment = Assessment::create([
        'module_id' => $this->module->id,
        'learning_unit_id' => $this->learningUnit->id,
        'title' => 'Asesmen Bertahap',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ]);

    $multipleChoice = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Pilih sumber energi terbarukan.',
        'question_type' => 'multiple_choice',
        'question_group' => 'pilihan_ganda_biasa',
        'options' => ['A' => 'Batu bara', 'B' => 'Matahari'],
        'correct_answer' => ['B'],
        'weight' => 10,
        'order' => 1,
    ]);

    $complexChoice = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'Pilih energi terbarukan.',
        'question_type' => 'complex_multiple_choice',
        'question_group' => 'pilihan_ganda_kompleks',
        'options' => ['A' => 'Surya', 'B' => 'Angin', 'C' => 'Batu bara'],
        'correct_answer' => ['A', 'B'],
        'weight' => 10,
        'order' => 2,
    ]);

    $component = Livewire::actingAs($this->student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->assertSee(QuestionGroupService::GROUP_LABELS['pilihan_ganda_biasa'])
        ->assertSee('Pilih sumber energi terbarukan.')
        ->assertDontSee('Pilih energi terbarukan.')
        ->set("answers.{$multipleChoice->id}", 'B')
        ->call('saveCurrentGroup')
        ->assertHasNoErrors()
        ->assertSet('savedQuestionGroups.pilihan_ganda_biasa', true);

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)->firstOrFail();

    expect(StudentAnswer::where('assessment_attempt_id', $attempt->id)
        ->where('question_id', $multipleChoice->id)
        ->firstOrFail()
        ->answer_text)->toBe('B');

    $component
        ->call('nextGroup')
        ->assertSet('currentGroupIndex', 1)
        ->assertSee(QuestionGroupService::GROUP_LABELS['pilihan_ganda_kompleks'])
        ->assertSee('Pilih energi terbarukan.')
        ->assertDontSee('Pilih sumber energi terbarukan.')
        ->set("answers.{$complexChoice->id}", ['A', 'B'])
        ->call('submit')
        ->assertHasNoErrors();

    $attempt->refresh();

    expect((float) $attempt->total_score)->toBe(20.0)
        ->and(StudentAnswer::where('assessment_attempt_id', $attempt->id)->count())->toBe(2)
        ->and(StudentAnswer::where('question_id', $complexChoice->id)->firstOrFail()->answer_json)->toBe(['A', 'B']);
});

/**
 * @return array{0: Assessment, 1: Question}
 */
function createAssessmentFlowFixture(object $testCase, array $assessmentOverrides = [], array $questionOverrides = []): array
{
    $assessment = Assessment::create(array_merge([
        'module_id' => $testCase->module->id,
        'learning_unit_id' => $testCase->learningUnit->id,
        'title' => 'Asesmen Energi',
        'kktp' => 75,
        'max_attempts' => 2,
        'is_published' => true,
    ], $assessmentOverrides));

    $question = Question::create(array_merge([
        'assessment_id' => $assessment->id,
        'question_text' => 'Pilih sumber energi terbarukan.',
        'question_type' => 'multiple_choice',
        'options' => ['A' => 'Batu bara', 'B' => 'Matahari'],
        'correct_answer' => ['B'],
        'weight' => 10,
    ], $questionOverrides));

    return [$assessment, $question];
}
