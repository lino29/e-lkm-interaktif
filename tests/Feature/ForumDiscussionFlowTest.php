<?php

use App\Livewire\Guru\ManageDiscussions;
use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('murid can create discussion and reply in a published learning unit', function () {
    [$teacher, $student, $learningUnit] = createDiscussionFixture();
    $forum = createForumActivityForDiscussionTest($learningUnit);

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $forum->id])
        ->set('answer_text', 'Bagaimana cara menghemat energi di kelas?')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Bagaimana cara menghemat energi di kelas?');

    $discussion = Discussion::where('learning_unit_id', $learningUnit->id)->firstOrFail();

    Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'parent_id' => $discussion->id,
        'title' => 'Balasan diskusi',
        'body' => 'Mulai dari mematikan lampu saat tidak digunakan.',
        'type' => 'reply',
    ]);

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $forum->id])
        ->assertSee('Mulai dari mematikan lampu saat tidak digunakan.');

    expect($discussion->replies()->count())->toBe(1)
        ->and($teacher->can('view', $discussion))->toBeTrue();
});

test('guru only sees discussions from their own modules', function () {
    [$teacher, $student, $learningUnit] = createDiscussionFixture();
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');
    $otherStudent = User::factory()->create();
    $otherStudent->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Diskusi Lain', 'code' => 'IPAS-DISKUSI-LAIN']);
    $otherModule = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $otherTeacher->id,
        'title' => 'Modul Diskusi Guru Lain',
        'slug' => 'modul-diskusi-guru-lain',
        'status' => 'published',
    ]);
    $otherLearningUnit = LearningUnit::create([
        'module_id' => $otherModule->id,
        'title' => 'KB Diskusi Guru Lain',
        'slug' => 'kb-diskusi-guru-lain',
    ]);

    Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'title' => 'Diskusi Modul Sendiri',
        'body' => 'Komentar modul milik guru login.',
    ]);
    Discussion::create([
        'learning_unit_id' => $otherLearningUnit->id,
        'user_id' => $otherStudent->id,
        'title' => 'Diskusi Modul Lain',
        'body' => 'Komentar modul guru lain.',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageDiscussions::class)
        ->assertSee('Komentar modul milik guru login.')
        ->assertDontSee('Komentar modul guru lain.');
});

test('guru can filter and moderate discussions from owned modules', function () {
    [$teacher, $student, $learningUnit, $module] = createDiscussionFixture();
    $forum = createForumActivityForDiscussionTest($learningUnit);

    $discussion = Discussion::create([
        'learning_unit_id' => $learningUnit->id,
        'user_id' => $student->id,
        'title' => 'Diskusi Moderasi',
        'body' => 'Komentar perlu dipin.',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageDiscussions::class)
        ->set('module_id', $module->id)
        ->set('learning_unit_id', $learningUnit->id)
        ->assertSee('Komentar perlu dipin.')
        ->call('togglePinned', $discussion->id)
        ->set("replyBodies.{$discussion->id}", 'Feedback guru: cek kembali data pengamatan.')
        ->call('replyToDiscussion', $discussion->id)
        ->set("participationScores.{$discussion->id}", 85)
        ->set("participationFeedbacks.{$discussion->id}", 'Refleksi sudah memakai data pengamatan.')
        ->call('reviewParticipation', $discussion->id)
        ->assertHasNoErrors();

    expect($discussion->fresh()->is_pinned)->toBeTrue()
        ->and($discussion->fresh()->participation_score)->toBe(85)
        ->and($discussion->fresh()->participation_feedback)->toBe('Refleksi sudah memakai data pengamatan.')
        ->and($discussion->replies()->where('body', 'Feedback guru: cek kembali data pengamatan.')->exists())->toBeTrue();

    ActivityAnswer::create([
        'activity_id' => $forum->id,
        'user_id' => $student->id,
        'answer_text' => 'Komentar perlu dipin.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $forum->id])
        ->assertSee('Feedback guru: cek kembali data pengamatan.');
});

/**
 * @return array{0: User, 1: User, 2: LearningUnit, 3: Module}
 */
function createDiscussionFixture(): array
{
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Diskusi', 'code' => 'IPAS-DISKUSI']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Diskusi',
        'slug' => 'modul-diskusi',
        'status' => 'published',
    ]);
    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Diskusi',
        'slug' => 'kb-diskusi',
    ]);

    return [$teacher, $student, $learningUnit, $module];
}

function createForumActivityForDiscussionTest(LearningUnit $learningUnit): Activity
{
    return Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Forum Diskusi/Refleksi',
        'phase' => 'forum_diskusi',
        'input_type' => 'discussion',
        'is_required' => true,
        'order' => 1,
        'validation_rules' => ['required' => true],
    ]);
}
