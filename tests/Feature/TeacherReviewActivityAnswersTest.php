<?php

use App\Livewire\Guru\ReviewActivityAnswers;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('teacher can review submitted activity answer', function () {
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

    Livewire::actingAs($teacher)
        ->test(ReviewActivityAnswers::class)
        ->assertSee('Ini jawaban menalar.')
        ->call('saveReview', $answer->id, 85, 'Bagus sekali.')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('activity_answers', [
        'id' => $answer->id,
        'status' => 'reviewed',
        'score' => 85,
        'teacher_feedback' => 'Bagus sekali.',
        'reviewed_by' => $teacher->id,
    ]);
});
