<?php

use App\Models\LearningUnit;
use App\Services\Learning\DynamicOutlineService;
use Database\Seeders\DatabaseSeeder;

test('dynamic outline can move sections up and down while normalizing order', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $service = app(DynamicOutlineService::class);
    $parent = $service->createSection($unit, [
        'title' => 'Group Baru',
        'section_type' => 'material_group',
    ]);
    $first = $service->createSection($unit, [
        'parent_id' => $parent->id,
        'title' => 'Pertama',
        'section_type' => 'custom_content',
    ]);
    $second = $service->createSection($unit, [
        'parent_id' => $parent->id,
        'title' => 'Kedua',
        'section_type' => 'custom_content',
    ]);

    $service->moveSectionUp($second);

    expect($second->fresh()->order)->toBe(1)
        ->and($first->fresh()->order)->toBe(2)
        ->and($second->fresh()->parent_id)->toBe($parent->id);

    $service->moveSectionDown($second->fresh());

    expect($first->fresh()->order)->toBe(1)
        ->and($second->fresh()->order)->toBe(2);
});
