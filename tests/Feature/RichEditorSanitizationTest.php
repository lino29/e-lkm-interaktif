<?php

use App\Services\Learning\DynamicOutlineService;

test('rich editor content is sanitized without stripping safe learning markup', function () {
    $html = '<h2>Judul</h2><p onclick="alert(1)">Paragraf</p><script>alert(2)</script><table><tr><td>Data</td></tr></table>';

    $sanitized = app(DynamicOutlineService::class)->sanitizeContent($html);

    expect($sanitized)->toContain('<h2>Judul</h2>')
        ->and($sanitized)->toContain('<p>Paragraf</p>')
        ->and($sanitized)->toContain('<table>')
        ->and($sanitized)->not->toContain('<script')
        ->and($sanitized)->not->toContain('onclick');
});
