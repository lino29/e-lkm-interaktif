<?php

use App\Livewire\Murid\LearningUnitPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use App\Services\Learning\LearningUnitOutlineService;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('stepper locks subsequent activities until previous is submitted', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('guru');

    $student = User::factory()->create();
    $student->assignRole('murid');

    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS-TEST']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Modul Test',
        'slug' => 'modul-test',
        'status' => 'published',
    ]);

    $learningUnit = LearningUnit::create([
        'module_id' => $module->id,
        'title' => 'KB Test',
        'slug' => 'kb-test',
        'order' => 1,
    ]);

    $activity1 = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Mengamati',
        'phase' => 'ayo_mengamati',
        'is_required' => true,
        'order' => 1,
    ]);

    $activity2 = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Menanya',
        'phase' => 'ayo_bertanya',
        'is_required' => true,
        'order' => 2,
    ]);

    app(LearningUnitOutlineService::class)->ensureDefaultOutline($learningUnit);
    $activityGroupId = $learningUnit->sections()->where('section_type', 'activity_group')->firstOrFail()->id;

    $component = Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $learningUnit->id])
        ->call('openSection', $activityGroupId);

    // Activity 1 should be unlocked, Activity 2 should be locked
    $component->assertSee('Mengamati')
        ->assertSee('Kerjakan')
        ->assertSee('Terkunci');

    // Draft should NOT unlock
    ActivityAnswer::create([
        'activity_id' => $activity1->id,
        'user_id' => $student->id,
        'status' => 'draft',
        'answer_text' => 'Draft',
    ]);

    $component = Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $learningUnit->id])
        ->call('openSection', $activityGroupId);
    $component->assertSee('Terkunci');

    // Submit SHOULD unlock
    ActivityAnswer::where('activity_id', $activity1->id)->update(['status' => 'submitted', 'submitted_at' => now()]);

    $component = Livewire::actingAs($student)
        ->test(LearningUnitPage::class, ['learningUnit' => $learningUnit->id])
        ->call('openSection', $activityGroupId);
    $component->assertDontSee('Terkunci');
});
