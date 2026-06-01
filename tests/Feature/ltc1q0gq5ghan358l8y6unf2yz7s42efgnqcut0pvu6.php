<?php

use App\Livewire\Murid\LearningUnitPage;
use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('student learning unit page renders the OITLINE outline sidebar', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $unit))
        ->assertOk()
        ->assertSee('Outline Kegiatan Belajar')
        ->assertSee('1. Tujuan Pembelajaran')
        ->assertSee('5. Forum Diskusi/Refleksi')
        ->assertSee('6. Asesmen Formatif');
});

test('student learning unit page defaults to learning objectives', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::with('rootSections')->orderBy('order')->firstOrFail();
    $objectiveSection = $unit->rootSections->firstWhere('slug', 'tujuan-pembelajaran');

    Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $unit->id])
        ->assertSet('activeSectionId', $objectiveSection->id)
        ->assertSee('1. Tujuan Pembelajaran');
});

test('material outline has oitline submaterials for kb1', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::where('order', 1)->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $unit))
        ->assertOk()
        ->assertSee('Konsep Energi')
        ->assertSee('Bentuk Energi')
        ->assertSee('Perubahan Energi')
        ->assertSee('Sumber Energi');
});
