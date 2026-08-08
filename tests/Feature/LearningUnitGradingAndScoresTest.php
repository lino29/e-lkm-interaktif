<?php

use App\Livewire\Guru\ReviewActivityAnswers;
use App\Livewire\Murid\MyScores;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\LearningUnitGrade;
use App\Models\LearningUnitSection;
use App\Models\Module;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\User;
use App\Services\Learning\ProgressService;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole('guru');

    $this->student = User::factory()->create();
    $this->student->assignRole('murid');

    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS-GRADE']);
    $this->module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $this->teacher->id,
        'title' => 'Energi Terbarukan',
        'slug' => 'energi-terbarukan-grade',
        'status' => 'published',
    ]);
});

test('teacher is notified once when a student learning unit is ready for review', function () {
    $learningUnit = LearningUnit::create([
        'module_id' => $this->module->id,
        'title' => 'Kegiatan Belajar 1',
        'slug' => 'kegiatan-belajar-1',
        'order' => 1,
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Ayo Menalar',
        'phase' => 'ayo_menalar',
        'input_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    LearningUnitSection::create([
        'learning_unit_id' => $learningUnit->id,
        'section_type' => 'activity',
        'title' => 'Ayo Menalar',
        'linked_model_type' => Activity::class,
        'linked_model_id' => $activity->id,
        'order' => 1,
        'is_visible' => true,
        'is_required' => true,
    ]);
    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $this->student->id,
        'answer_text' => 'Energi surya dapat mengurangi penggunaan bahan bakar fosil.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $progressService = app(ProgressService::class);
    $progressService->refreshLearningUnitProgress($this->student, $learningUnit);
    $progressService->refreshLearningUnitProgress($this->student, $learningUnit);

    $grade = LearningUnitGrade::where('learning_unit_id', $learningUnit->id)
        ->where('student_id', $this->student->id)
        ->firstOrFail();

    $notification = $this->teacher->notifications()->sole();

    expect($grade->score)->toBeNull()
        ->and($notification->read_at)->toBeNull()
        ->and($notification->data['learning_unit_grade_id'])->toBe($grade->id)
        ->and($notification->data['student_id'])->toBe($this->student->id);

    Livewire::actingAs($this->teacher)
        ->test(ReviewActivityAnswers::class)
        ->call('selectSubmission', $grade->id);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('teacher grades all answers in one learning unit with maximum score twenty', function () {
    $learningUnit = LearningUnit::create([
        'module_id' => $this->module->id,
        'title' => 'Kegiatan Belajar 2',
        'slug' => 'kegiatan-belajar-2',
        'order' => 2,
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Ayo Mengamati',
        'phase' => 'ayo_mengamati',
        'input_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $this->student->id,
        'answer_text' => 'Saya mengamati panel surya menghasilkan listrik saat terkena cahaya.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    $grade = LearningUnitGrade::create([
        'learning_unit_id' => $learningUnit->id,
        'student_id' => $this->student->id,
    ]);

    $component = Livewire::actingAs($this->teacher)
        ->test(ReviewActivityAnswers::class)
        ->assertSee('Kegiatan Belajar 2')
        ->call('selectSubmission', $grade->id)
        ->assertSee('Saya mengamati panel surya')
        ->set('gradeScore', '21')
        ->call('saveGrade')
        ->assertHasErrors(['gradeScore']);

    $component
        ->set('gradeScore', '20')
        ->set('gradeFeedback', 'Pengamatan lengkap dan kesimpulan sudah tepat.')
        ->call('saveGrade')
        ->assertHasNoErrors();

    expect($grade->fresh())
        ->score->toBe('20.00')
        ->feedback->toBe('Pengamatan lengkap dan kesimpulan sudah tepat.')
        ->reviewed_by->toBe($this->teacher->id)
        ->reviewed_at->not->toBeNull();
});

test('student sees only submitted summative attempts and can open the selected answer detail', function () {
    $summative = Assessment::create([
        'module_id' => $this->module->id,
        'title' => 'Asesmen Sumatif Energi',
        'type' => 'final',
        'is_published' => true,
    ]);
    $formative = Assessment::create([
        'module_id' => $this->module->id,
        'title' => 'Asesmen Formatif Tersembunyi dari Nilai Sumatif',
        'type' => 'formative',
        'is_published' => true,
    ]);
    $question = Question::create([
        'assessment_id' => $summative->id,
        'question_text' => 'Sebutkan satu manfaat energi surya.',
        'question_type' => 'essay',
        'weight' => 100,
        'order' => 1,
    ]);
    $attempt = AssessmentAttempt::create([
        'assessment_id' => $summative->id,
        'student_id' => $this->student->id,
        'attempt_number' => 1,
        'total_score' => 90,
        'max_score' => 100,
        'status' => 'tuntas',
        'submitted_at' => now(),
    ]);
    AssessmentAttempt::create([
        'assessment_id' => $formative->id,
        'student_id' => $this->student->id,
        'attempt_number' => 1,
        'total_score' => 80,
        'max_score' => 100,
        'status' => 'tuntas',
        'submitted_at' => now(),
    ]);
    StudentAnswer::create([
        'assessment_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'student_id' => $this->student->id,
        'answer_text' => 'Mengurangi emisi saat menghasilkan listrik.',
        'score' => 90,
    ]);

    Livewire::actingAs($this->student)
        ->test(MyScores::class)
        ->assertSee('Asesmen Sumatif Energi')
        ->assertDontSee('Asesmen Formatif Tersembunyi dari Nilai Sumatif')
        ->call('showAssessmentDetail', $attempt->id)
        ->assertSee('Mengurangi emisi saat menghasilkan listrik.');
});

test('five learning unit scores total one hundred and another student cannot open the detail', function () {
    $otherStudent = User::factory()->create();
    $otherStudent->assignRole('murid');

    foreach (range(1, 5) as $order) {
        $learningUnit = LearningUnit::create([
            'module_id' => $this->module->id,
            'title' => 'Kegiatan Belajar '.$order,
            'slug' => 'nilai-kb-'.$order,
            'order' => $order,
        ]);
        LearningUnitGrade::create([
            'learning_unit_id' => $learningUnit->id,
            'student_id' => $this->student->id,
            'reviewed_by' => $this->teacher->id,
            'score' => 20,
            'feedback' => 'Tuntas.',
            'reviewed_at' => now(),
        ]);
    }

    $firstGrade = LearningUnitGrade::where('student_id', $this->student->id)->firstOrFail();

    Livewire::actingAs($this->student)
        ->test(MyScores::class)
        ->call('showTab', 'kb')
        ->assertSee('100')
        ->assertSee('dari 20');

    expect(fn () => Livewire::actingAs($otherStudent)
        ->test(MyScores::class)
        ->call('showLearningUnitDetail', $firstGrade->id))
        ->toThrow(ModelNotFoundException::class);
});
