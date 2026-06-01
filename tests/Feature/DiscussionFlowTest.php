<?php

use App\Livewire\Guru\ManageDiscussions;
use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\Discussion;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'guru']);
    Role::create(['name' => 'murid']);
});

test('murid can post discussion and guru can reply', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $murid = User::factory()->create();
    $murid->assignRole('murid');

    $subject = Subject::create(['name' => 'Test Subject', 'code' => 'TEST01']);

    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $guru->id,
        'title' => 'Test Modul',
        'slug' => 'test-modul',
        'status' => 'published',
    ]);

    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'Kegiatan Belajar 1',
        'slug' => 'kegiatan-belajar-1',
        'order' => 1,
    ]);

    $forum = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Forum Diskusi/Refleksi',
        'phase' => 'forum_diskusi',
        'input_type' => 'discussion',
        'is_required' => true,
        'order' => 1,
        'validation_rules' => ['required' => true],
    ]);

    // Murid posts
    Livewire::actingAs($murid)
        ->test(ActivityPage::class, ['activity' => $forum->id])
        ->set('answer_text', 'Apa itu energi?')
        ->call('submit');

    $discussion = Discussion::where('user_id', $murid->id)->first();
    expect($discussion->body)->toBe('Apa itu energi?');

    // Guru replies
    Livewire::actingAs($guru)
        ->test(ManageDiscussions::class)
        ->set("replyBodies.{$discussion->id}", 'Energi adalah kemampuan untuk melakukan kerja.')
        ->call('replyToDiscussion', $discussion->id);

    $reply = Discussion::where('parent_id', $discussion->id)->first();
    expect($reply->body)->toBe('Energi adalah kemampuan untuk melakukan kerja.')
        ->and($reply->user_id)->toBe($guru->id);
});
