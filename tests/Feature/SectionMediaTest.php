<?php

use App\Livewire\Murid\LearningUnitPage;
use App\Models\LearningUnit;
use App\Models\Media;
use App\Models\User;
use App\Services\Learning\DynamicOutlineService;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('media can be attached to a learning unit section and rendered for students', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $section = app(DynamicOutlineService::class)->createSection($unit, [
        'title' => 'Galeri Energi Surya',
        'section_type' => 'media_gallery',
    ]);

    Media::create([
        'learning_unit_id' => $unit->id,
        'learning_unit_section_id' => $section->id,
        'title' => 'Panel Surya',
        'type' => 'image',
        'url' => 'https://example.com/panel.jpg',
        'order' => 1,
    ]);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();

    Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $unit->id])
        ->call('openSection', $section->id)
        ->assertSee('Galeri Energi Surya')
        ->assertSee('Panel Surya');
});
