<?php

use App\Livewire\Guru\ManageLearningUnitOutline;
use App\Models\LearningUnit;
use App\Models\User;
use App\Services\Learning\DynamicOutlineService;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('teacher outline editor uses friendly copy and modal flow', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnitOutline::class, ['learningUnit' => $unit->id])
        ->assertSee('Struktur Kegiatan Belajar')
        ->assertSee('Tambah Bagian')
        ->assertSee('Tujuan Pembelajaran')
        ->assertSee('Pengaturan Lanjutan')
        ->assertSee('Zona Berbahaya')
        ->call('openAddSectionModal')
        ->assertSet('showCreateModal', true)
        ->assertSee('Konten Bebas')
        ->set('newSectionType', 'media_gallery')
        ->call('createSectionFromModal')
        ->assertSet('showCreateModal', false)
        ->assertSee('Galeri Media');
});

test('media form is hidden until teacher opens the add media modal', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnitOutline::class, ['learningUnit' => $unit->id])
        ->assertSee('Media Pendukung')
        ->assertDontSee('Judul media')
        ->call('openMediaModal')
        ->assertSet('showMediaModal', true)
        ->assertSee('Judul media');
});

test('rich editor is only rendered for rich outline sections', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $outline = app(DynamicOutlineService::class);

    $material = $outline->createSection($unit, [
        'title' => 'Pengertian Energi Fosil',
        'section_type' => 'material_item',
    ]);

    $keyPoints = $outline->createSection($unit, [
        'title' => 'Pokok Materi',
        'section_type' => 'key_points',
        'content_json' => [
            'concepts' => ['Energi'],
            'facts' => [],
            'procedures' => [],
            'metacognitive' => [],
        ],
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnitOutline::class, ['learningUnit' => $unit->id])
        ->call('selectSection', $material->id)
        ->assertSeeHtml('data-rich-editor')
        ->assertSeeHtml('rich-editor-wrapper-material-content-editor-'.$material->id)
        ->assertSeeHtml('rich-editor:sync')
        ->assertSeeHtml('material-content-editor-'.$material->id)
        ->call('selectSection', $keyPoints->id)
        ->assertDontSeeHtml('data-rich-editor')
        ->assertSee('Konsep')
        ->assertSee('Fakta');
});

test('teacher can delete an outline section with its sub sections', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $outline = app(DynamicOutlineService::class);

    $parent = $outline->createSection($unit, [
        'title' => 'Bagian Sementara',
        'section_type' => 'custom_content',
        'editor_type' => 'rich_text',
    ]);

    $child = $outline->createSection($unit, [
        'parent_id' => $parent->id,
        'title' => 'Subbagian Sementara',
        'section_type' => 'custom_content',
        'editor_type' => 'rich_text',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnitOutline::class, ['learningUnit' => $unit->id])
        ->call('selectSection', $parent->id)
        ->call('deleteSection', $parent->id)
        ->assertHasNoErrors()
        ->assertDontSee('Bagian Sementara')
        ->assertDontSee('Subbagian Sementara');

    expect($parent->fresh())->toBeNull()
        ->and($child->fresh())->toBeNull();
});

test('rich editor content is sanitized when saved from outline editor', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();
    $section = app(DynamicOutlineService::class)->createSection($unit, [
        'title' => 'Pengertian Energi Fosil',
        'section_type' => 'material_item',
        'content_json' => ['source' => 'outline'],
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnitOutline::class, ['learningUnit' => $unit->id])
        ->call('selectSection', $section->id)
        ->set('form.content', '<h2 onclick="alert(1)">Energi Fosil</h2><p><a href="javascript:alert(1)">tautan</a><script>alert(1)</script></p>')
        ->call('saveSection')
        ->assertHasNoErrors();

    $section->refresh();

    expect($section->content)
        ->toContain('<h2>Energi Fosil</h2>')
        ->not->toContain('onclick')
        ->not->toContain('<script')
        ->not->toContain('javascript:');

    expect($section->content_json)->toBe(['source' => 'outline']);
});
