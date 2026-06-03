<?php

use App\Livewire\Guru\ManageActivities;
use App\Models\Activity;
use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('teacher can upload observation image and video media for ayo mengamati activities', function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    $uploads = [
        'image' => UploadedFile::fake()->image('panel-surya.png'),
        'video' => UploadedFile::fake()->create('turbin-angin.mp4', 2048, 'video/mp4'),
    ];

    foreach ($uploads as $expectedType => $file) {
        $activity = Activity::create([
            'learning_unit_id' => $unit->id,
            'title' => 'Ayo Mengamati '.str($expectedType)->headline(),
            'phase' => 'ayo_mengamati',
            'prompt' => 'Amati media berikut.',
            'input_type' => 'essay',
            'order' => 20 + count($unit->activities()->get()),
            'is_required' => true,
            'requires_teacher_review' => false,
        ]);

        Livewire::actingAs($teacher)
            ->test(ManageActivities::class)
            ->call('edit', $activity->id)
            ->set('mediaFile', $file)
            ->set('mediaTitle', 'Media Pengamatan '.str($expectedType)->headline())
            ->set('mediaCaption', 'Caption media '.$expectedType)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Aktivitas dan media pengamatan berhasil disimpan.');

        $activity->refresh();

        expect($activity->media_path)->toStartWith('activity-media/')
            ->and($activity->display_config['media_type'])->toBe($expectedType)
            ->and($activity->display_config['media_path'])->toBe($activity->media_path)
            ->and($activity->display_config['media_title'])->toBe('Media Pengamatan '.str($expectedType)->headline())
            ->and($activity->display_config['caption'])->toBe('Caption media '.$expectedType);

        Storage::disk('public')->assertExists($activity->media_path);
    }
});

test('observation upload form shows upload progress and completion notification UI', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageActivities::class)
        ->set('phase', 'ayo_mengamati')
        ->assertSee('Media Ayo Mengamati')
        ->assertSeeHtml('livewire-upload-progress')
        ->assertSee('Mengupload media')
        ->assertSee('Upload selesai. Klik Simpan Aktivitas untuk menyimpan media ke aktivitas.');
});
