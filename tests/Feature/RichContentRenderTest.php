<?php

use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

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
