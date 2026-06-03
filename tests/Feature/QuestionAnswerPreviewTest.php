<?php

use App\Models\Question;

test('multiple choice preview displays selectable answer options', function () {
    $view = $this->blade('<x-learning.question-answer-preview :question="$question" />', [
        'question' => new Question([
            'question_type' => 'multiple_choice',
            'options' => [
                'A' => 'Energi tidak dapat diciptakan atau dimusnahkan.',
                'B' => 'Energi hanya muncul saat bergerak.',
            ],
        ]),
    ]);

    $view->assertSee('A');
    $view->assertSee('Energi tidak dapat diciptakan atau dimusnahkan.');
    $view->assertSee('Energi hanya muncul saat bergerak.');
});

test('complex multiple choice preview displays checkbox style options', function () {
    $view = $this->blade('<x-learning.question-answer-preview :question="$question" />', [
        'question' => new Question([
            'question_type' => 'complex_multiple_choice',
            'options' => [
                'Panel surya mengubah cahaya menjadi listrik.',
                'Baterai dapat menyimpan energi listrik.',
            ],
        ]),
    ]);

    $view->assertSee('Murid dapat memilih lebih dari satu jawaban.');
    $view->assertSee('Panel surya mengubah cahaya menjadi listrik.');
    $view->assertSee('Baterai dapat menyimpan energi listrik.');
});

test('true false preview displays fallback true and false answers', function () {
    $view = $this->blade('<x-learning.question-answer-preview :question="$question" />', [
        'question' => new Question([
            'question_type' => 'true_false',
            'options' => null,
        ]),
    ]);

    $view->assertSee('Benar');
    $view->assertSee('Salah');
});

test('matching preview displays prompts and answer pool', function () {
    $view = $this->blade('<x-learning.question-answer-preview :question="$question" />', [
        'question' => new Question([
            'question_type' => 'matching',
            'options' => [
                'left' => ['Lampu', 'Kipas angin'],
                'right' => ['Energi cahaya', 'Energi gerak'],
            ],
        ]),
    ]);

    $view->assertSee('Lampu');
    $view->assertSee('Kipas angin');
    $view->assertSee('Pilih pasangan');
    $view->assertSee('Energi cahaya');
    $view->assertSee('Energi gerak');
});

test('short answer and essay previews display answer fields', function (string $type, string $label) {
    $view = $this->blade('<x-learning.question-answer-preview :question="$question" />', [
        'question' => new Question([
            'question_type' => $type,
        ]),
    ]);

    $view->assertSee($label);
})->with([
    'short answer' => ['short_answer', 'Jawaban singkat'],
    'essay' => ['essay', 'Jawaban uraian'],
]);
