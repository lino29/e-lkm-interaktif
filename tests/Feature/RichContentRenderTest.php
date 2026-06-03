<?php

use App\Livewire\Murid\LearningUnitPage;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('student learning content renders ckeditor html inside learning content wrapper', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::query()->with('rootSections')->orderBy('order')->firstOrFail();
    $section = $unit->rootSections->firstOrFail();

    $section->update([
        'content' => '<h2>Heading dari Word</h2><figure class="image"><img src="/storage/editor-images/panel.webp" alt="Panel"><figcaption>Caption gambar</figcaption></figure><table><tbody><tr><td>Data tabel</td></tr></tbody></table>',
    ]);

    $this
        ->actingAs($student)
        ->get(route('murid.learning-units.show', $unit))
        ->assertOk()
        ->assertSee('ck-content learning-content', false)
        ->assertSee('<h2>Heading dari Word</h2>', false)
        ->assertSee('<figcaption>Caption gambar</figcaption>', false)
        ->assertSee('<table>', false)
        ->assertSee('Data tabel');
});

test('student material item prefers teacher edited section content over linked material content', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $section = LearningUnitSection::query()
        ->where('section_type', 'material_item')
        ->where('linked_model_type', Material::class)
        ->whereNotNull('linked_model_id')
        ->firstOrFail();

    Material::query()
        ->whereKey($section->linked_model_id)
        ->update(['content' => '<p>Konten lama dari bank materi.</p>']);

    $section->update([
        'content' => '<h2>Isi Materi dari Editor Guru</h2><p>Konten ini harus tampil untuk murid.</p>',
    ]);

    Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $section->learning_unit_id])
        ->call('openSection', $section->id)
        ->assertSee('Isi Materi dari Editor Guru')
        ->assertSee('Konten ini harus tampil untuk murid.')
        ->assertDontSee('Konten lama dari bank materi.');
});
