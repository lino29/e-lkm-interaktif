<?php

use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('teacher can preview a later learning unit without progress records', function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $subject = Subject::create(['name' => 'IPAS Teacher Access', 'code' => 'IPAS-GURU-ACCESS']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Teacher Access',
        'slug' => 'modul-teacher-access',
        'status' => 'published',
    ]);
    LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Pertama',
        'slug' => 'kb-pertama-teacher-access',
        'order' => 1,
    ]);
    $laterLearningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Bebas Diakses',
        'slug' => 'kb-bebas-diakses-guru',
        'order' => 2,
    ]);

    $this->actingAs($teacher)
        ->get(route('guru.learning-units.preview', $laterLearningUnit))
        ->assertOk()
        ->assertSee($laterLearningUnit->title);
});
