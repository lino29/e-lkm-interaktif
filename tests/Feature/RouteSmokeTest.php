<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('route list boots without errors', function () {
    expect(Artisan::call('route:list', ['--except-vendor' => true]))->toBe(0);
});

test('admin can open primary admin routes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    foreach ([
        'admin.dashboard',
        'admin.users',
        'admin.teachers',
        'admin.students',
        'admin.classes',
        'admin.subjects',
        'admin.reports',
    ] as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk();
    }
});

test('guru can open primary guru routes', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    foreach ([
        'guru.dashboard',
        'guru.modules',
        'guru.learning-units',
        'guru.materials',
        'guru.activities',
        'guru.discussions',
        'guru.assessments',
        'guru.questions',
        'guru.rubrics',
        'guru.projects',
        'guru.reports',
    ] as $routeName) {
        $this->actingAs($guru)
            ->get(route($routeName))
            ->assertOk();
    }
});

test('murid can open primary murid routes', function () {
    $murid = User::factory()->create();
    $murid->assignRole('murid');

    foreach ([
        'murid.dashboard',
        'murid.modules',
        'murid.remedial',
        'murid.project',
        'murid.scores',
        'murid.portfolio',
    ] as $routeName) {
        $this->actingAs($murid)
            ->get(route($routeName))
            ->assertOk();
    }
});

test('roles cannot open dashboards for other roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $guru = User::factory()->create();
    $guru->assignRole('guru');
    $murid = User::factory()->create();
    $murid->assignRole('murid');

    $this->actingAs($admin)->get(route('guru.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('murid.dashboard'))->assertForbidden();
    $this->actingAs($guru)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($guru)->get(route('murid.dashboard'))->assertForbidden();
    $this->actingAs($murid)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($murid)->get(route('guru.dashboard'))->assertForbidden();
});
