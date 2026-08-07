<?php

use App\Models\Assessment;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('demo learning seeder creates complete renewable energy module', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::with('glossaries', 'references', 'assessments.questions.keywords', 'assessments.questions.rubrics', 'learningUnits.materials', 'learningUnits.media', 'learningUnits.activities', 'learningUnits.assessments.questions.keywords', 'learningUnits.assessments.questions.rubrics')
        ->where('slug', 'energi-terbarukan')
        ->firstOrFail();

    expect($module->learningUnits)->toHaveCount(5)
        ->and($module->glossaries)->not->toBeEmpty()
        ->and($module->references)->not->toBeEmpty();

    foreach ($module->learningUnits as $learningUnit) {
        expect($learningUnit->objectives)->not->toBeEmpty()
            ->and($learningUnit->materials->count())->toBeGreaterThanOrEqual(5)
            ->and($learningUnit->materials->first()->content)->not->toContain('placeholder')
            ->and($learningUnit->media)->toHaveCount(1)
            ->and($learningUnit->media->first()->file_path)->toStartWith('demo/media/energi-terbarukan/')
            ->and($learningUnit->media->first()->url)->toBeNull()
            ->and($learningUnit->media->first()->embed_code)->toContain('Caption:')
            ->and($learningUnit->assessments)->not->toBeEmpty();

        $phases = $learningUnit->activities->pluck('phase')->all();

        expect($phases)->toContain('ayo_mengamati')
            ->toContain('ayo_bertanya')
            ->toContain('ayo_mencoba')
            ->toContain('ayo_menalar')
            ->toContain('ayo_menyimpulkan')
            ->toContain('forum_diskusi');

        expect($learningUnit->activities->pluck('prompt')->implode(' '))
            ->not->toContain('Tuliskan hasil pengamatan Anda pada kolom di bawah ini.');

        $questions = $learningUnit->assessments->first()->questions;

        expect($questions->pluck('question_type')->all())
            ->toContain('multiple_choice')
            ->toContain('true_false')
            ->toContain('short_answer')
            ->toContain('essay')
            ->toContain('complex_multiple_choice')
            ->toContain('matching');

        $essay = $questions->firstWhere('question_type', 'essay');
        $shortAnswer = $questions->firstWhere('question_type', 'short_answer');

        expect($essay->reference_answer)->not->toBeEmpty()
            ->and($essay->keywords)->not->toBeEmpty()
            ->and($essay->rubrics)->not->toBeEmpty()
            ->and($shortAnswer->keywords)->not->toBeEmpty();
    }

    $finalAssessment = $module->assessments->firstWhere('type', 'final');
    expect($finalAssessment)->not->toBeNull()
        ->and($finalAssessment->is_published)->toBeTrue()
        ->and($finalAssessment->learning_unit_id)->toBeNull()
        ->and($finalAssessment->questions->pluck('question_type')->all())
        ->toContain('multiple_choice')
        ->toContain('true_false')
        ->toContain('short_answer')
        ->toContain('essay')
        ->toContain('complex_multiple_choice')
        ->toContain('matching');

    $finalEssay = $finalAssessment->questions->firstWhere('question_type', 'essay');
    expect($finalEssay->reference_answer)->not->toBeEmpty()
        ->and($finalEssay->keywords)->not->toBeEmpty()
        ->and($finalEssay->rubrics)->not->toBeEmpty();
});

test('demo student can open the last generated learning unit without prior progress', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::with('learningUnits')->where('slug', 'energi-terbarukan')->firstOrFail();
    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $lastLearningUnit = $module->learningUnits->sortByDesc('order')->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $lastLearningUnit))
        ->assertOk()
        ->assertSee($lastLearningUnit->title);
});

test('demo student can open the final assessment without completing learning units', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $finalAssessment = Assessment::where('module_id', $module->id)->where('type', 'final')->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.assessments.show', $finalAssessment))
        ->assertOk()
        ->assertSee('Asesmen Akhir Modul Energi Terbarukan');

    expect(Assessment::where('module_id', $module->id)->where('type', 'formative')->count())->toBeGreaterThanOrEqual(5)
        ->and(Assessment::where('module_id', $module->id)->where('type', 'final')->count())->toBe(1);
});
