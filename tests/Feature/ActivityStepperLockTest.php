<?php

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use App\Services\Learning\LearningUnitOutlineService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('learning unit navigation keeps every activity open regardless of answer status', function () {
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
    $activitySection = $learningUnit->sections()
        ->where('linked_model_id', $activity2->id)
        ->firstOrFail();

    ActivityAnswer::create([
        'activity_id' => $activity1->id,
        'user_id' => $student->id,
        'status' => 'draft',
        'answer_text' => 'Draft',
    ]);

    $html = Blade::render(
        '<x-learning.activity-section-card :section="$section" :activity-statuses="$activityStatuses" />',
        [
            'section' => $activitySection,
            'activityStatuses' => [
                $activity1->id => ['status' => 'draft'],
                $activity2->id => ['status' => 'belum_mulai'],
            ],
        ],
    );

    expect($html)
        ->toContain('Menanya')
        ->toContain('Kerjakan')
        ->not->toContain('Terkunci');
});
