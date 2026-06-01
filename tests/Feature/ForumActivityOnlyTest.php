<?php

use App\Livewire\Murid\ActivityPage;
use App\Livewire\Murid\LearningUnitPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

test('forum is submitted through forum activity only', function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Forum Activity', 'code' => 'IPAS-FORUM-ACT']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Module Forum Activity',
        'slug' => 'module-forum-activity',
        'status' => 'published',
    ]);
    $unit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Forum Activity',
        'slug' => 'kb-forum-activity',
        'order' => 1,
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Forum Diskusi/Refleksi',
        'phase' => 'forum_diskusi',
        'input_type' => 'discussion',
        'is_required' => true,
        'order' => 1,
        'validation_rules' => ['required' => true],
    ]);

    expect(method_exists(LearningUnitPage::class, 'submitDiscussion'))->toBeFalse()
        ->and(method_exists(LearningUnitPage::class, 'replyToDiscussion'))->toBeFalse();

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->set('answer_text', 'Saya akan menghemat listrik kelas dengan mematikan lampu saat cahaya cukup.')
        ->call('submit')
        ->assertHasNoErrors();

    expect(ActivityAnswer::where('activity_id', $activity->id)->where('user_id', $student->id)->exists())->toBeTrue()
        ->and(Discussion::where('learning_unit_id', $unit->id)->where('user_id', $student->id)->exists())->toBeTrue();
});
