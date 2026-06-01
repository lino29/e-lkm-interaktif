<?php

use App\Models\LearningUnit;
use App\Models\Module;
use App\Services\Learning\LearningUnitOutlineService;
use Database\Seeders\DatabaseSeeder;

test('creates default outline for every renewable energy learning unit', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();

    foreach ($module->learningUnits as $unit) {
        expect($unit->rootSections()->count())->toBe(6);
    }
});

test('has OITLINE root sections in correct order', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::orderBy('order')->firstOrFail();

    expect($unit->rootSections()->pluck('title')->toArray())
        ->toBe(LearningUnitOutlineService::ROOT_TITLES);
});

test('links five learning activities, forum, and question groups', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::with('activities')->orderBy('order')->firstOrFail();

    expect($unit->activities()->count())->toBeGreaterThanOrEqual(6)
        ->and($unit->activities()->where('phase', 'ayo_mengamati')->exists())->toBeTrue()
        ->and($unit->activities()->where('phase', 'ayo_bertanya')->exists())->toBeTrue()
        ->and($unit->activities()->where('phase', 'ayo_mencoba')->exists())->toBeTrue()
        ->and($unit->activities()->where('phase', 'ayo_menalar')->exists())->toBeTrue()
        ->and($unit->activities()->where('phase', 'ayo_menyimpulkan')->exists())->toBeTrue()
        ->and($unit->activities()->where('phase', 'forum_diskusi')->exists())->toBeTrue();

    expect($unit->sections()->where('section_type', 'question_group')->pluck('slug')->toArray())
        ->toContain('pilihan_ganda_biasa')
        ->toContain('pilihan_ganda_kompleks')
        ->toContain('benar_salah')
        ->toContain('isian_uraian_singkat')
        ->toContain('menjodohkan');
});
