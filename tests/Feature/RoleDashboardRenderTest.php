<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('role dashboards render their dashboard content', function (string $role, string $route, string $heading, array $expectedNavigation) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->actingAs($user)
        ->get(route($route));

    $response->assertOk()
        ->assertSee($heading);

    foreach ($expectedNavigation as $navigationLabel) {
        $response->assertSee($navigationLabel);
    }
})->with([
    'admin' => ['admin', 'admin.dashboard', 'Dashboard Admin', ['Pengguna', 'Kelas', 'Mapel', 'Laporan']],
    'guru' => ['guru', 'guru.dashboard', 'Dashboard Guru', ['Modul', 'Kegiatan', 'Asesmen', 'Laporan']],
    'murid' => ['murid', 'murid.dashboard', 'Dashboard Murid', ['Modul Saya', 'Nilai', 'Remedial', 'Portofolio']],
]);

test('users cannot access another role dashboard', function (string $role, string $forbiddenRoute) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route($forbiddenRoute))
        ->assertForbidden();
})->with([
    'admin cannot access guru dashboard' => ['admin', 'guru.dashboard'],
    'admin cannot access murid dashboard' => ['admin', 'murid.dashboard'],
    'guru cannot access admin dashboard' => ['guru', 'admin.dashboard'],
    'guru cannot access murid dashboard' => ['guru', 'murid.dashboard'],
    'murid cannot access admin dashboard' => ['murid', 'admin.dashboard'],
    'murid cannot access guru dashboard' => ['murid', 'guru.dashboard'],
]);
