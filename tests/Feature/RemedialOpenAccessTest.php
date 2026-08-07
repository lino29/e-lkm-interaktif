<?php

use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('remedial progress does not block access to the next learning unit', function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Remedial Access', 'code' => 'IPAS-REM-ACCESS']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Remedial Access',
        'slug' => 'modul-remedial-access',
        'status' => 'published',
    ]);
    $firstUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Remedial',
        'slug' => 'kb-remedial-access',
        'order' => 1,
    ]);
    $secondUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Setelah Remedial',
        'slug' => 'kb-setelah-remedial',
        'order' => 2,
    ]);

    Progress::create([
        'user_id' => $student->id,
        'module_id' => $module->id,
        'learning_unit_id' => $firstUnit->id,
        'status' => 'remedial',
    ]);

    $this->actingAs($student)
        ->get(route('murid.learning-units.show', $secondUnit))
        ->assertOk();
});
