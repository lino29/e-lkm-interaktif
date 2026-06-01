<?php

use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('module detail shows introduction and closing content from oitline', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertOk()
        ->assertSee('Pendahuluan Modul')
        ->assertSee('Prakata')
        ->assertSee('Daftar Isi')
        ->assertSee('Deskripsi Singkat')
        ->assertSee('Capaian Pembelajaran')
        ->assertSee('Tujuan Pembelajaran')
        ->assertSee('Relevansi')
        ->assertSee('Petunjuk Belajar')
        ->assertSee('Penutup Modul')
        ->assertSee('Rangkuman')
        ->assertSee('Daftar Istilah')
        ->assertSee('Daftar Pustaka');
});
