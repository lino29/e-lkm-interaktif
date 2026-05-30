<?php

use App\Livewire\Guru\ManageModules;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('guru modules route is protected by guru role', function () {
    $student = User::factory()->create();
    $student->assignRole('murid');

    $this->actingAs($student)
        ->get(route('guru.modules'))
        ->assertForbidden();
});

test('guru only sees modules they created', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');
    $subject = Subject::create([
        'name' => 'Projek IPAS',
        'code' => 'IPAS',
    ]);

    Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Energi Surya',
        'slug' => 'modul-energi-surya',
        'status' => 'draft',
    ]);

    Module::create([
        'subject_id' => $subject->id,
        'created_by' => $otherTeacher->id,
        'title' => 'Modul Guru Lain',
        'slug' => 'modul-guru-lain',
        'status' => 'published',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageModules::class)
        ->assertSee('Modul Energi Surya')
        ->assertDontSee('Modul Guru Lain');
});

test('guru can create and update module through livewire', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $subject = Subject::create([
        'name' => 'Projek IPAS',
        'code' => 'IPAS',
    ]);

    $component = Livewire::actingAs($teacher)
        ->test(ManageModules::class)
        ->set('subject_id', $subject->id)
        ->set('title', 'Modul Energi Terbarukan')
        ->set('introduction', 'Pendahuluan singkat modul.')
        ->set('learning_objectives', 'Murid memahami energi terbarukan.')
        ->set('status', 'draft')
        ->set('kktp', 76)
        ->set('max_attempts', 3)
        ->call('save')
        ->assertHasNoErrors();

    $module = Module::where('created_by', $teacher->id)->firstOrFail();

    expect($module->title)->toBe('Modul Energi Terbarukan')
        ->and($module->slug)->toBe('modul-energi-terbarukan')
        ->and($module->status)->toBe('draft')
        ->and($module->kktp)->toBe(76)
        ->and($module->max_attempts)->toBe(3);

    $component
        ->call('edit', $module->id)
        ->set('title', 'Modul Energi Terbarukan Revisi')
        ->set('status', 'published')
        ->set('kktp', 80)
        ->call('save')
        ->assertHasNoErrors();

    expect($module->fresh())
        ->title->toBe('Modul Energi Terbarukan Revisi')
        ->slug->toBe('modul-energi-terbarukan')
        ->status->toBe('published')
        ->kktp->toBe(80);
});
