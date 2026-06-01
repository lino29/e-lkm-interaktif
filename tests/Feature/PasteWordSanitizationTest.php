<?php

use App\Models\LearningUnit;
use App\Services\Learning\DynamicOutlineService;
use Database\Seeders\DatabaseSeeder;

test('word paste markup is preserved while unsafe html is stripped', function () {
    $html = <<<'HTML'
        <h1 style="font-family: Calibri; color: #1f4e79; position: absolute">Energi Surya</h1>
        <p class="MsoNormal" onclick="alert(1)">
            <span style="font-size: 14pt; background-color: #fff2cc">Panel surya</span>
            <strong>mengubah</strong> <em>cahaya</em> menjadi listrik.
        </p>
        <ul><li>Fotovoltaik</li><li>Inverter</li></ul>
        <table style="border-collapse: collapse"><tbody><tr><th colspan="2">Komponen</th></tr><tr><td style="text-align: center">PV</td><td>DC</td></tr></tbody></table>
        <figure class="image"><img src="/storage/editor-images/panel.webp" alt="Panel surya" width="640" height="360" onload="alert(2)"><figcaption>Gambar 1. Panel surya</figcaption></figure>
        <p><a href="javascript:alert(3)" title="Tidak aman">Link berbahaya</a></p>
        <iframe src="https://www.youtube.com/embed/abc123" width="560" height="315"></iframe>
        <iframe src="https://evil.test/embed/abc123"></iframe>
        <script>alert(4)</script>
        HTML;

    $sanitized = app(DynamicOutlineService::class)->sanitizeContent($html);

    expect($sanitized)
        ->toContain('<h1')
        ->toContain('Energi Surya')
        ->toContain('font-family')
        ->toContain('color:#1f4e79')
        ->toContain('<strong>mengubah</strong>')
        ->toContain('<em>cahaya</em>')
        ->toContain('<ul>')
        ->toContain('<table')
        ->toContain('colspan="2"')
        ->toContain('<figure')
        ->toContain('<figcaption>Gambar 1. Panel surya</figcaption>')
        ->toContain('<img')
        ->toContain('src="/storage/editor-images/panel.webp"')
        ->toContain('https://www.youtube.com/embed/abc123')
        ->not->toContain('<script')
        ->not->toContain('onclick')
        ->not->toContain('onload')
        ->not->toContain('javascript:')
        ->not->toContain('evil.test')
        ->not->toContain('<iframe></iframe>')
        ->not->toContain('position');
});

test('rich text section content is stored as sanitized html', function () {
    $this->seed(DatabaseSeeder::class);

    $unit = LearningUnit::query()->firstOrFail();
    $section = app(DynamicOutlineService::class)->createSection($unit, [
        'section_type' => 'custom_content',
        'editor_type' => 'rich_text',
        'title' => 'Konten Word',
        'content' => '<h2>Judul Word</h2><p onclick="alert(1)"><strong>Tebal</strong></p><script>alert(2)</script>',
    ]);

    expect($section->content)
        ->toContain('<h2>Judul Word</h2>')
        ->toContain('<strong>Tebal</strong>')
        ->not->toContain('onclick')
        ->not->toContain('<script');
});
