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
