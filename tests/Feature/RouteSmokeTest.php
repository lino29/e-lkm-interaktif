<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'guru']);
    Role::create(['name' => 'murid']);
});

test('admin routes are accessible by admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('guru routes are accessible by guru', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $response = $this->actingAs($guru)->get('/guru/dashboard');
    $response->assertStatus(200);
});

test('murid routes are accessible by murid', function () {
    $murid = User::factory()->create();
    $murid->assignRole('murid');

    $response = $this->actingAs($murid)->get('/murid/dashboard');
    $response->assertStatus(200);
});
