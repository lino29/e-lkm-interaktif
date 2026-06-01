<?php

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('student cannot bypass activity order through direct route', function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $student = User::factory()->create();
    $student->assignRole('murid');
    $subject = Subject::create(['name' => 'IPAS Activity Lock', 'code' => 'IPAS-ACT-LOCK']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Module Activity Lock',
        'slug' => 'module-activity-lock',
        'status' => 'published',
    ]);
    $unit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Activity Lock',
        'slug' => 'kb-activity-lock',
        'order' => 1,
    ]);
    $first = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Ayo Mengamati',
        'phase' => 'ayo_mengamati',
        'input_type' => 'essay',
        'is_required' => true,
        'order' => 1,
    ]);
    $second = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Ayo Menyimpulkan',
        'phase' => 'ayo_menyimpulkan',
        'input_type' => 'essay',
        'is_required' => true,
        'order' => 2,
    ]);

    $this->actingAs($student)
        ->get(route('murid.activities.show', $second))
        ->assertForbidden();

    ActivityAnswer::create([
        'activity_id' => $first->id,
        'user_id' => $student->id,
        'answer_text' => 'Jawaban pengamatan lengkap.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('murid.activities.show', $second))
        ->assertOk();
});
