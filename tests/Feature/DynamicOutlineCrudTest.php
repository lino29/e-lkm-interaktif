<?php

use App\Models\LearningUnit;
use App\Services\Learning\DynamicOutlineService;
use Database\Seeders\DatabaseSeeder;

test('dynamic outline service can create update duplicate hide and delete sections', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $service = app(DynamicOutlineService::class);

    $root = $service->createSection($unit, [
        'title' => 'Konten Custom',
        'section_type' => 'custom_content',
        'content' => '<h2>Aman</h2><script>alert("x")</script>',
    ]);

    $child = $service->createSection($unit, [
        'parent_id' => $root->id,
        'title' => 'Child Custom',
        'section_type' => 'custom_content',
        'content' => '<p>Isi child</p>',
    ]);

    $updated = $service->updateSection($root, [
        'title' => 'Konten Custom Update',
        'content' => '<p onclick="bad()">Isi update</p>',
    ]);

    $copy = $service->duplicateSection($updated);
    $hidden = $service->toggleVisibility($updated);
    $service->deleteSection($child);

    expect($updated->fresh()->title)->toBe('Konten Custom Update')
        ->and($updated->fresh()->content)->not->toContain('<script')
        ->and($updated->fresh()->content)->not->toContain('onclick')
        ->and($copy->title)->toContain('Copy')
        ->and($copy->children)->toHaveCount(1)
        ->and($hidden->is_visible)->toBeFalse()
        ->and($child->fresh())->toBeNull();
});
