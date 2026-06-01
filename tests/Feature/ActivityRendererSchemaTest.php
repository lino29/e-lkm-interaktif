<?php

use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('activity table renderer supports preset rows and computed columns', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    $activity = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Percobaan Suhu',
        'phase' => 'ayo_mencoba',
        'prompt' => 'Catat perubahan suhu.',
        'input_type' => 'table',
        'answer_schema' => [
            'columns' => [
                ['name' => 'no', 'label' => 'No', 'type' => 'readonly_text'],
                ['name' => 'suhu_awal', 'label' => 'Suhu Awal', 'type' => 'number', 'required' => true],
                ['name' => 'suhu_akhir', 'label' => 'Suhu Akhir', 'type' => 'number', 'required' => true],
                ['name' => 'perubahan_suhu', 'label' => 'Perubahan Suhu', 'type' => 'computed', 'formula' => 'suhu_akhir - suhu_awal'],
            ],
            'preset_rows' => [['no' => 1]],
            'min_rows' => 1,
        ],
        'validation_rules' => ['required' => true],
        'is_required' => true,
        'order' => 1,
    ]);

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->assertSet('answer_json.0.no', 1)
        ->set('answer_json.0.suhu_awal', 26)
        ->set('answer_json.0.suhu_akhir', 33)
        ->call('submit')
        ->assertHasNoErrors();

    $answer = ActivityAnswer::where('activity_id', $activity->id)->where('user_id', $student->id)->firstOrFail();

    expect((float) $answer->answer_json[0]['perubahan_suhu'])->toBe(7.0);
});
