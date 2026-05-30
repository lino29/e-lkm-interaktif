<?php

use App\Livewire\Guru\ManageProjects;
use App\Livewire\Murid\MyProject;
use App\Models\Module;
use App\Models\Project;
use App\Models\Subject;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'guru']);
    Role::create(['name' => 'murid']);
});

test('murid can save draft and submit project, then guru can review', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $murid = User::factory()->create();
    $murid->assignRole('murid');

    $subject = Subject::create(['name' => 'Test Subject', 'code' => 'TEST01']);

    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $guru->id,
        'title' => 'Test Modul',
        'slug' => 'test-modul',
        'status' => 'published',
    ]);

    // Murid saves draft
    Livewire::actingAs($murid)
        ->test(MyProject::class)
        ->set('module_id', $module->id)
        ->set('project_title', 'My Awesome Project')
        ->set('problem', 'Energy crisis')
        ->call('save', 'draft');

    $project = Project::where('user_id', $murid->id)->first();
    expect($project->status)->toBe('draft')
        ->and($project->problem)->toBe('Energy crisis');

    // Murid submits
    Livewire::actingAs($murid)
        ->test(MyProject::class)
        ->set('module_id', $module->id)
        ->set('project_title', 'My Awesome Project')
        ->set('problem', 'Energy crisis solved')
        ->call('save', 'submitted');

    $project->refresh();
    expect($project->status)->toBe('submitted')
        ->and($project->problem)->toBe('Energy crisis solved');

    // Guru reviews
    Livewire::actingAs($guru)
        ->test(ManageProjects::class)
        ->call('review', $project->id)
        ->set('score', 95.5)
        ->set('feedback', 'Great job!')
        ->call('saveReview');

    $project->refresh();
    expect($project->status)->toBe('reviewed')
        ->and($project->score)->toEqual(95.5)
        ->and($project->feedback)->toBe('Great job!');

    // Murid cannot edit after reviewed (should abort 403)
    Livewire::actingAs($murid)
        ->test(MyProject::class)
        ->set('module_id', $module->id)
        ->set('project_title', 'Hacked Project')
        ->call('save', 'submitted')
        ->assertForbidden();
});
