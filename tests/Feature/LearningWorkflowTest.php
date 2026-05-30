<?php

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('teacher can create module records and student can view published modules', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS']);

    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'E-LKM Energi Terbarukan',
        'slug' => 'elkm-energi-terbarukan',
        'status' => 'published',
    ]);
    $unit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'Konsep Energi',
        'slug' => 'konsep-energi',
        'order' => 1,
    ]);
    Material::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Materi Energi',
        'content' => 'Energi adalah kemampuan melakukan usaha.',
    ]);
    $activity = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Ayo Mengamati',
        'phase' => 'ayo_mengamati',
    ]);
    Assessment::create([
        'module_id' => $module->id,
        'learning_unit_id' => $unit->id,
        'title' => 'Asesmen Konsep',
        'is_published' => true,
    ]);

    $this->actingAs($student)
        ->get(route('murid.modules'))
        ->assertOk()
        ->assertSee('E-LKM Energi Terbarukan');

    $this->actingAs($teacher)
        ->get(route('guru.modules.show', $module))
        ->assertOk()
        ->assertSee('Konsep Energi');

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertOk()
        ->assertSee('Pendahuluan');

    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'answer_text' => 'Saya mengamati penggunaan listrik di kelas.',
        'submitted_at' => now(),
    ]);

    expect(ActivityAnswer::where('user_id', $student->id)->exists())->toBeTrue();
});

test('student cannot view draft modules or unpublished assessments', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS-DRAFT']);

    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Draft Energi',
        'slug' => 'draft-energi',
        'status' => 'draft',
    ]);
    $assessment = Assessment::create([
        'module_id' => $module->id,
        'title' => 'Asesmen Draft',
        'is_published' => false,
    ]);

    $this->actingAs($student)
        ->get(route('murid.modules.show', $module))
        ->assertNotFound();

    $module->update(['status' => 'published']);

    $this->actingAs($student)
        ->get(route('murid.assessments.show', $assessment))
        ->assertNotFound();
});
