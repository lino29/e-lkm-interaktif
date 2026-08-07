<?php

use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Guru\Reports as GuruReports;
use App\Models\Module;
use App\Models\Subject;
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

test('admin can download a module report PDF through Livewire', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $subject = Subject::create([
        'name' => 'PDF Livewire',
        'code' => 'PDF-LIVEWIRE',
    ]);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $admin->id,
        'title' => 'Modul PDF Livewire',
        'slug' => 'modul-pdf-livewire',
        'status' => 'published',
    ]);

    Livewire::actingAs($admin)
        ->test(AdminReports::class)
        ->set('module_id', $module->id)
        ->call('exportPdf')
        ->assertFileDownloaded('Laporan_E-LKM_modul-pdf-livewire.pdf', null, 'application/pdf');
});
