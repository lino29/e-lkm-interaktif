<?php

use App\Livewire\Guru\ReviewActivityAnswers;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\LearningUnitGrade;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('teacher can review submitted activity answers as one learning unit grade', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $student = User::factory()->create();
    $student->assignRole('murid');

    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS-TEST']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Test',
        'slug' => 'modul-test',
        'status' => 'published',
    ]);

    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Test',
        'slug' => 'kb-test',
        'order' => 1,
    ]);

    $activity = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Menalar',
        'phase' => 'ayo_menalar',
        'is_required' => true,
        'order' => 1,
    ]);

    $answer = ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'status' => 'submitted',
        'answer_text' => 'Ini jawaban menalar.',
        'submitted_at' => now(),
    ]);

    $grade = LearningUnitGrade::create([
        'learning_unit_id' => $learningUnit->id,
        'student_id' => $student->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(ReviewActivityAnswers::class)
        ->call('selectSubmission', $grade->id)
        ->assertSee('Ini jawaban menalar.')
        ->set('gradeScore', '20')
        ->set('gradeFeedback', 'Bagus sekali.')
        ->call('saveGrade')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('learning_unit_grades', [
        'id' => $grade->id,
        'score' => 20,
        'feedback' => 'Bagus sekali.',
        'reviewed_by' => $teacher->id,
    ]);
});
