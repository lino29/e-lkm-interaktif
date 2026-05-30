<?php

use App\Livewire\Guru\ManageLearningUnits;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function createModuleFor(User $teacher, string $title): Module
{
    $subject = Subject::firstOrCreate(
        ['code' => 'IPAS'],
        ['name' => 'Projek IPAS']
    );

    return Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => $title,
        'slug' => str($title)->slug()->toString(),
        'status' => 'draft',
    ]);
}

test('guru learning units route is protected by guru role', function () {
    $student = User::factory()->create();
    $student->assignRole('murid');

    $this->actingAs($student)
        ->get(route('guru.learning-units'))
        ->assertForbidden();
});

test('guru only sees learning units from their modules', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');

    $teacherModule = createModuleFor($teacher, 'Modul Energi Surya');
    $otherModule = createModuleFor($otherTeacher, 'Modul Guru Lain');

    LearningUnit::create([
        'module_id' => $teacherModule->id,
        'title' => 'Kegiatan Surya',
        'slug' => 'kegiatan-surya',
        'order' => 1,
    ]);

    LearningUnit::create([
        'module_id' => $otherModule->id,
        'title' => 'Kegiatan Guru Lain',
        'slug' => 'kegiatan-guru-lain',
        'order' => 1,
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnits::class)
        ->assertSee('Kegiatan Surya')
        ->assertDontSee('Kegiatan Guru Lain');
});

test('guru can create and update learning unit through livewire', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $module = createModuleFor($teacher, 'Modul Energi Terbarukan');

    $component = Livewire::actingAs($teacher)
        ->test(ManageLearningUnits::class)
        ->set('module_id', $module->id)
        ->set('title', 'Kegiatan Belajar Energi Surya')
        ->set('description', 'Mengenal energi surya.')
        ->set('objectives', 'Murid memahami pemanfaatan energi surya.')
        ->set('order', 2)
        ->call('save')
        ->assertHasNoErrors();

    $learningUnit = LearningUnit::where('module_id', $module->id)->firstOrFail();

    expect($learningUnit->title)->toBe('Kegiatan Belajar Energi Surya')
        ->and($learningUnit->slug)->toBe('kegiatan-belajar-energi-surya')
        ->and($learningUnit->description)->toBe('Mengenal energi surya.')
        ->and($learningUnit->objectives)->toBe('Murid memahami pemanfaatan energi surya.')
        ->and($learningUnit->order)->toBe(2);

    $component
        ->call('edit', $learningUnit->id)
        ->set('title', 'Kegiatan Belajar Energi Surya Revisi')
        ->set('order', 3)
        ->call('save')
        ->assertHasNoErrors();

    expect($learningUnit->fresh())
        ->title->toBe('Kegiatan Belajar Energi Surya Revisi')
        ->slug->toBe('kegiatan-belajar-energi-surya-revisi')
        ->order->toBe(3);
});

test('guru cannot create learning unit for another teachers module', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('guru');
    $otherModule = createModuleFor($otherTeacher, 'Modul Guru Lain');

    Livewire::actingAs($teacher)
        ->test(ManageLearningUnits::class)
        ->set('module_id', $otherModule->id)
        ->set('title', 'Kegiatan Tidak Sah')
        ->set('order', 1)
        ->call('save')
        ->assertHasErrors(['module_id']);

    expect(LearningUnit::where('title', 'Kegiatan Tidak Sah')->exists())->toBeFalse();
});
