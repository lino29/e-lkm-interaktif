<?php

use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Guru\Reports as GuruReports;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'guru']);
    Role::firstOrCreate(['name' => 'murid']);
});

test('admin can render reports page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AdminReports::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.admin.reports');
});

test('guru can render reports page', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    Livewire::actingAs($guru)
        ->test(GuruReports::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.guru.reports');
});
