<?php

use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('only teachers can access outline management routes', function () {
    $this->seed(DatabaseSeeder::class);

    $teacher = User::where('email', 'guru@elkm.test')->firstOrFail();
    $admin = User::where('email', 'admin@elkm.test')->firstOrFail();
    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    $this->actingAs($student)
        ->get(route('guru.learning-units.outline', $unit))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('guru.modules.outline', $module))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('guru.learning-units.outline', $unit))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('guru.modules.outline', $module))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('guru.learning-units.preview', $unit))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.learning-units.outline', $unit))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.modules.outline', $module))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.learning-units.preview', $unit))
        ->assertOk();
});
