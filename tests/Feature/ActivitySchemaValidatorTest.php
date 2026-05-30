<?php

use App\Models\Activity;
use App\Services\Learning\ActivitySchemaValidator;

test('validates minimum words correctly', function () {
    $activity = new Activity([
        'input_type' => 'essay',
        'is_required' => true,
        'validation_rules' => ['required' => true, 'min_words' => 5],
    ]);

    $validator = app(ActivitySchemaValidator::class);

    // Invalid (4 words)
    $result = $validator->validate($activity, null, 'Satu dua tiga empat');
    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Jawaban minimal terdiri dari 5 kata. Saat ini: 4 kata.');

    // Valid (5 words)
    $result = $validator->validate($activity, null, 'Satu dua tiga empat lima');
    expect($result['valid'])->toBeTrue();
});

test('validates maximum words correctly', function () {
    $activity = new Activity([
        'input_type' => 'short_text',
        'is_required' => true,
        'validation_rules' => ['required' => true, 'max_words' => 5],
    ]);

    $validator = app(ActivitySchemaValidator::class);

    // Invalid (6 words)
    $result = $validator->validate($activity, null, 'Satu dua tiga empat lima enam');
    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Jawaban maksimal terdiri dari 5 kata. Saat ini: 6 kata.');

    // Valid (5 words)
    $result = $validator->validate($activity, null, 'Satu dua tiga empat lima');
    expect($result['valid'])->toBeTrue();
});

test('validates table rows minimum and column required', function () {
    $activity = new Activity([
        'input_type' => 'table',
        'is_required' => true,
        'answer_schema' => [
            'min_rows' => 2,
            'columns' => [
                ['name' => 'alat', 'label' => 'Alat', 'type' => 'text', 'required' => true],
            ],
        ],
    ]);

    $validator = app(ActivitySchemaValidator::class);

    // Invalid (Only 1 row)
    $result = $validator->validate($activity, [['alat' => 'Lampu']], null);
    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Tabel minimal harus diisi dengan 2 baris jawaban.');

    // Invalid (2 rows, but second row missing required field)
    $result = $validator->validate($activity, [['alat' => 'Lampu'], ['alat' => '']], null);
    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain("Baris 2: Kolom 'Alat' wajib diisi.");

    // Valid
    $result = $validator->validate($activity, [['alat' => 'Lampu'], ['alat' => 'Setrika']], null);
    expect($result['valid'])->toBeTrue();
});
