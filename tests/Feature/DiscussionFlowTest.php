<?php

use App\Livewire\Guru\ManageDiscussions;
use App\Livewire\Murid\LearningUnitPage;
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
    ]);

    // Murid posts
    Livewire::actingAs($murid)
        ->test(LearningUnitPage::class, ['learningUnit' => $learningUnit->id])
        ->set('discussionBody', 'Apa itu energi?')
        ->call('submitDiscussion');

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
