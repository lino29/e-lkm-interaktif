<?php

use App\Models\LearningUnit;
use App\Services\Learning\DynamicOutlineService;
use App\Services\Learning\LearningUnitOutlineService;
use Database\Seeders\DatabaseSeeder;

test('generating oitline template does not delete custom sections or overwrite teacher content', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $service = app(DynamicOutlineService::class);
    $custom = $service->createSection($unit, [
        'title' => 'Catatan Guru',
        'section_type' => 'custom_content',
        'content' => '<p>Konten custom.</p>',
    ]);
    $objective = $unit->sections()->where('slug', 'tujuan-pembelajaran')->firstOrFail();

    $service->updateSection($objective, [
        'content' => '<p>Tujuan versi guru.</p>',
    ]);

    app(LearningUnitOutlineService::class)->ensureDefaultOutline($unit);

    expect($custom->fresh())->not->toBeNull()
        ->and($objective->fresh()->content)->toContain('Tujuan versi guru.');
});
