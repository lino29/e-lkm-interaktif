<?php

use App\Models\Assessment;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('demo learning seeder creates complete renewable energy module', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::with('learningUnits.materials', 'learningUnits.media', 'learningUnits.activities', 'learningUnits.assessments.questions')
        ->where('slug', 'energi-terbarukan')
        ->firstOrFail();

    expect($module->learningUnits)->toHaveCount(5);

    foreach ($module->learningUnits as $learningUnit) {
        expect($learningUnit->objectives)->not->toBeEmpty()
            ->and($learningUnit->materials)->toHaveCount(1)
            ->and($learningUnit->materials->first()->content)->not->toContain('placeholder')
            ->and($learningUnit->media)->toHaveCount(1)
            ->and($learningUnit->assessments)->not->toBeEmpty();

        $phases = $learningUnit->activities->pluck('phase')->all();

        expect($phases)->toContain('ayo_mengamati')
            ->toContain('ayo_bertanya')
            ->toContain('ayo_mencoba')
            ->toContain('ayo_menalar')
            ->toContain('ayo_menyimpulkan')
            ->toContain('forum_diskusi');

        expect($learningUnit->assessments->first()->questions)->not->toBeEmpty();
    }
});

test('demo users can open the generated learning structure', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::with('learningUnits')->where('slug', 'energi-terbarukan')->firstOrFail();
    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();

    foreach ($module->learningUnits as $learningUnit) {
        $this->actingAs($student)
            ->get(route('murid.learning-units.show', $learningUnit))
            ->assertOk()
            ->assertSee($learningUnit->title);
    }

    $this->actingAs($teacher)
        ->get(route('guru.modules.show', $module))
        ->assertOk()
        ->assertSee('KB1 Konsep Energi dan Sumber Energi');

    expect(Assessment::where('module_id', $module->id)->where('type', 'formative')->count())->toBeGreaterThanOrEqual(5);
});
