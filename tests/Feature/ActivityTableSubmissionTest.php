<?php

use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('murid can submit table array data to ayo mencoba activity', function () {
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

    $activity = Activity::create([
        'learning_unit_id' => $learningUnit->id,
        'title' => 'Ayo Mencoba',
        'phase' => 'ayo_mencoba',
        'input_type' => 'table',
        'is_required' => true,
        'answer_schema' => [
            'columns' => [
                ['name' => 'alat', 'label' => 'Alat', 'type' => 'text'],
                ['name' => 'energi', 'label' => 'Energi', 'type' => 'text'],
            ],
            'min_rows' => 2,
            'allow_add' => true,
        ],
    ]);

    $tableData = [
        ['alat' => 'Lampu', 'energi' => 'Listrik -> Cahaya'],
        ['alat' => 'Setrika', 'energi' => 'Listrik -> Panas'],
    ];

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->set('table_data', $tableData)
        ->call('submit')
        ->assertStatus(200)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('activity_answers', [
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'status' => 'submitted',
    ]);

    $answer = ActivityAnswer::where('activity_id', $activity->id)->where('user_id', $student->id)->first();
    expect($answer->answer_json)->toBeArray()
        ->and($answer->answer_json[0]['alat'])->toBe('Lampu')
        ->and($answer->answer_json[1]['energi'])->toBe('Listrik -> Panas');
});
