<?php

use App\Services\Learning\ActivityTemplateService;

it('returns correct template for ayo_mencoba', function () {
    $service = new ActivityTemplateService;
    $template = $service->getTemplateForPhase('ayo_mencoba');

    expect($template)
        ->toBeArray()
        ->and($template['input_type'])->toBe('table')
        ->and($template['title'])->toBe('Ayo Mencoba')
        ->and($template['answer_schema']['columns'])->toBeArray()
        ->and($template['requires_teacher_review'])->toBeFalse();
});

it('returns correct template for ayo_menalar', function () {
    $service = new ActivityTemplateService;
    $template = $service->getTemplateForPhase('ayo_menalar');

    expect($template)
        ->toBeArray()
        ->and($template['input_type'])->toBe('essay')
        ->and($template['requires_teacher_review'])->toBeTrue();
});

it('validates json schema correctly', function () {
    $service = new ActivityTemplateService;

    expect($service->isValidSchema('{"columns": []}'))->toBeTrue()
        ->and($service->isValidSchema('invalid json'))->toBeFalse()
        ->and($service->isValidSchema(''))->toBeTrue()
        ->and($service->isValidSchema(null))->toBeTrue();
});
