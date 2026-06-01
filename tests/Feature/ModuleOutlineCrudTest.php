<?php

use App\Livewire\Guru\ManageModuleOutline;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('teacher can edit module outline and student sees updated content', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $section = $module->sections()->where('slug', 'petunjuk-belajar')->firstOrFail();

    Livewire::actingAs($teacher)
        ->test(ManageModuleOutline::class, ['module' => $module->id])
        ->assertSee('Editor Section Modul')
        ->assertSee('Daftar Section Modul')
        ->call('edit', $section->id)
        ->set('content', '<h2>Petunjuk Baru</h2><p>Belajar berurutan.</p>')
        ->call('save')
        ->assertHasNoErrors();

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertOk()
        ->assertSee('Petunjuk Baru');
});
