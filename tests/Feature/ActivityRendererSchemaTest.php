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

test('seeded ayo mencoba schemas follow renewable energy outline', function () {
    $this->seed(DatabaseSeeder::class);

    $kb1 = LearningUnit::where('order', 1)->firstOrFail();
    $kb2 = LearningUnit::where('order', 2)->firstOrFail();
    $kb3 = LearningUnit::where('order', 3)->firstOrFail();
    $kb4 = LearningUnit::where('order', 4)->firstOrFail();

    $kb1Schema = Activity::where('learning_unit_id', $kb1->id)->where('phase', 'ayo_mencoba')->firstOrFail()->answer_schema;
    $kb2Activity = Activity::where('learning_unit_id', $kb2->id)->where('phase', 'ayo_mencoba')->firstOrFail();
    $kb3Schema = Activity::where('learning_unit_id', $kb3->id)->where('phase', 'ayo_mencoba')->firstOrFail()->answer_schema;
    $kb4Schema = Activity::where('learning_unit_id', $kb4->id)->where('phase', 'ayo_mencoba')->firstOrFail()->answer_schema;

    expect($kb1Schema['min_rows'])->toBe(10)
        ->and(collect($kb1Schema['columns'])->firstWhere('name', 'energi_masuk')['type'])->toBe('select')
        ->and(collect($kb1Schema['columns'])->firstWhere('name', 'sumber_energi')['type'])->toBe('select')
        ->and($kb2Activity->input_type)->toBe('fields')
        ->and(collect($kb2Activity->answer_schema['fields'])->pluck('name')->all())->toContain(
            'sumber_energi_fosil',
            'proses_pembakaran',
            'emisi',
            'dampak_lingkungan',
            'dampak_kesehatan',
            'dampak_ekonomi',
            'solusi',
        )
        ->and($kb3Schema['allow_add'])->toBeFalse()
        ->and($kb3Schema['allow_delete'])->toBeFalse()
        ->and($kb3Schema['preset_rows'])->toHaveCount(4)
        ->and($kb3Schema['preset_rows'][0]['kondisi'])->toBe('Banyak sinar matahari')
        ->and($kb4Schema['allow_add'])->toBeFalse()
        ->and($kb4Schema['allow_delete'])->toBeFalse()
        ->and($kb4Schema['preset_rows'])->toHaveCount(2)
        ->and(collect($kb4Schema['columns'])->pluck('name')->all())->toContain('media', 'suhu_awal', 'suhu_akhir', 'perubahan_suhu', 'catatan');
});

test('fixed table rows cannot be removed from locked schemas', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $unit = LearningUnit::orderBy('order')->firstOrFail();

    $activity = Activity::create([
        'learning_unit_id' => $unit->id,
        'title' => 'Tabel Terkunci',
        'phase' => 'ayo_mencoba',
        'prompt' => 'Isi tabel terkunci.',
        'input_type' => 'table',
        'answer_schema' => [
            'columns' => [
                ['name' => 'media', 'label' => 'Media', 'type' => 'readonly_text'],
                ['name' => 'catatan', 'label' => 'Catatan', 'type' => 'text', 'required' => true],
            ],
            'preset_rows' => [
                ['media' => 'Baris 1'],
                ['media' => 'Baris 2'],
            ],
            'min_rows' => 1,
            'allow_delete' => false,
        ],
        'validation_rules' => ['required' => true],
        'is_required' => true,
        'order' => 1,
    ]);

    $component = Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->call('removeTableRow', 0);

    expect($component->get('answer_json'))->toHaveCount(2);
});
