<?php

use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

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
