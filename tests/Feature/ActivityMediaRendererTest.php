<?php

use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('activity page renders observation media from display config', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::where('order', 1)->firstOrFail();
    $activity = Activity::where('learning_unit_id', $unit->id)
        ->where('phase', 'ayo_mengamati')
        ->firstOrFail();

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->assertSee('Media Pengamatan / Media Pendukung')
        ->assertSee('Media Pengamatan')
        ->assertSee('Amati contoh perubahan energi');
});

test('media renderer uses plyr hooks for video and youtube media', function () {
    $videoHtml = view('components.learning.media-renderer', [
        'type' => 'video',
        'filePath' => 'activity-media/demo.mp4',
        'url' => null,
        'embedCode' => null,
        'title' => 'Video Pengamatan',
        'caption' => null,
    ])->render();

    $youtubeHtml = view('components.learning.media-renderer', [
        'type' => 'youtube',
        'filePath' => null,
        'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'embedCode' => null,
        'title' => 'Video YouTube',
        'caption' => null,
    ])->render();

    expect($videoHtml)
        ->toContain('data-plyr-player')
        ->and($youtubeHtml)
        ->toContain('data-plyr-player')
        ->toContain('data-plyr-provider="youtube"')
        ->toContain('data-plyr-embed-id="dQw4w9WgXcQ"');
});
