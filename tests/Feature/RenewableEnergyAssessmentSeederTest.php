<?php

use App\Models\Module;
use App\Services\Assessment\QuestionGroupService;
use Database\Seeders\DatabaseSeeder;

test('every learning unit formative assessment has oitline question groups and minimum questions', function () {
    $this->seed(DatabaseSeeder::class);

    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();

    foreach ($module->learningUnits as $unit) {
        $assessment = $unit->assessments()->where('type', 'formative')->with('questions.keywords', 'questions.rubrics')->firstOrFail();
        $groupCounts = $assessment->questions->groupBy('question_group')->map->count();

        foreach (array_keys(QuestionGroupService::GROUP_LABELS) as $group) {
            expect($groupCounts[$group] ?? 0)->toBeGreaterThanOrEqual(2);
        }

        expect($assessment->questions)->toHaveCount(10)
            ->and($assessment->questions->where('question_type', 'short_answer')->first()?->keywords)->not->toBeEmpty()
            ->and($assessment->questions->where('question_type', 'essay')->first()?->rubrics)->not->toBeEmpty();
    }
});
