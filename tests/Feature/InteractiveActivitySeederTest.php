<?php

use App\Models\Module;
use Database\Seeders\DatabaseSeeder;

test('seeder configures ayo mencoba properly with table schema', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $learningUnit = $module->learningUnits()->where('order', 1)->firstOrFail();

    $ayoMencoba = $learningUnit->activities()->where('phase', 'ayo_mencoba')->firstOrFail();

    expect($ayoMencoba->input_type)->toBe('table')
        ->and($ayoMencoba->answer_schema)->not->toBeNull()
        ->and($ayoMencoba->answer_schema['columns'])->toBeArray()
        ->and($ayoMencoba->answer_schema['columns'][0]['name'])->toBe('no')
        ->and($ayoMencoba->answer_schema['min_rows'])->toBe(10);
});

test('seeder configures ayo menalar and menyimpulkan for teacher review', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $learningUnit = $module->learningUnits()->where('order', 1)->firstOrFail();

    $ayoMenalar = $learningUnit->activities()->where('phase', 'ayo_menalar')->firstOrFail();
    $ayoMenyimpulkan = $learningUnit->activities()->where('phase', 'ayo_menyimpulkan')->firstOrFail();
    $ayoMengamati = $learningUnit->activities()->where('phase', 'ayo_mengamati')->firstOrFail();

    expect($ayoMenalar->requires_teacher_review)->toBeTrue()
        ->and($ayoMenyimpulkan->requires_teacher_review)->toBeTrue()
        ->and($ayoMengamati->requires_teacher_review)->toBeFalse();
});
