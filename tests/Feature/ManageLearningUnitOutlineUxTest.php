<?php

use App\Livewire\Guru\ManageLearningUnitOutline;
use App\Models\LearningUnit;
use App\Models\User;
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
