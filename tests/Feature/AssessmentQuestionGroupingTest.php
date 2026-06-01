<?php

use App\Models\Assessment;
use App\Models\Module;
use App\Models\User;
use App\Services\Assessment\QuestionGroupService;
use Database\Seeders\DatabaseSeeder;

test('seeded formative questions are assigned to OITLINE groups', function () {
    $this->seed(DatabaseSeeder::class);

    $assessment = Assessment::where('type', 'formative')->with('questions')->firstOrFail();

    expect($assessment->questions->pluck('question_group')->unique()->values()->all())
        ->toContain('pilihan_ganda_biasa')
        ->toContain('pilihan_ganda_kompleks')
        ->toContain('benar_salah')
        ->toContain('isian_uraian_singkat')
        ->toContain('menjodohkan');
});

test('assessment page renders question group labels', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $assessment = Module::where('slug', 'energi-terbarukan')->firstOrFail()
        ->assessments()
        ->where('type', 'final')
        ->firstOrFail();

    $response = $this->actingAs($student)->get(route('murid.assessments.show', $assessment));

    foreach (QuestionGroupService::GROUP_LABELS as $label) {
        $response->assertSee($label);
    }
});
