<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('dashboard redirects users to their role dashboard', function (string $role, string $route) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($route));
})->with([
    ['admin', 'admin.dashboard'],
    ['guru', 'guru.dashboard'],
    ['murid', 'murid.dashboard'],
]);

test('role dashboards are protected from other roles', function () {
    $student = User::factory()->create();
    $student->assignRole('murid');

    $this->actingAs($student)
        ->get(route('guru.dashboard'))
        ->assertForbidden();
});
