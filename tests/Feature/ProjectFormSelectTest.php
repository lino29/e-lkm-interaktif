<?php

use App\Livewire\Murid\ActivityPage;
use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Project;
use App\Models\User;
use App\Services\Learning\ProgressService;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

test('kb5 project type is selected and stored on project draft', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $kb5 = LearningUnit::where('order', 5)->firstOrFail();

    LearningUnit::where('order', '<', 5)->get()->each(fn (LearningUnit $unit) => completeUnitForProjectSelectTest($student, $unit));

    $activity = Activity::where('learning_unit_id', $kb5->id)
        ->where('input_type', 'project_form')
        ->firstOrFail();

    Activity::where('learning_unit_id', $kb5->id)
        ->where('order', '<', $activity->order)
        ->get()
        ->each(fn (Activity $previousActivity) => ActivityAnswer::updateOrCreate(
            ['activity_id' => $previousActivity->id, 'user_id' => $student->id],
            ['answer_text' => 'Jawaban prasyarat KB5.', 'status' => 'submitted', 'submitted_at' => now()],
        ));

    expect(collect($activity->answer_schema['fields'])->firstWhere('name', 'project_type')['type'])->toBe('select');

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->set('field_data.project_type', 'Kompor surya mini')
        ->set('field_data.problem', 'Kantin membutuhkan pemanas sederhana tanpa listrik.')
        ->set('field_data.objective', 'Membuat model kompor surya mini.')
        ->set('field_data.tools_materials', 'Kardus, aluminium foil, plastik bening.')
        ->set('field_data.procedure', 'Rancang, rakit, uji suhu, dan catat data.')
        ->set('field_data.data_to_collect', 'Suhu awal, suhu akhir, waktu pengujian.')
        ->set('field_data.expected_result', 'Suhu meningkat saat terkena matahari.')
        ->call('submit')
        ->assertHasNoErrors();

    $project = Project::where('user_id', $student->id)->where('learning_unit_id', $kb5->id)->firstOrFail();

    expect($project->project_type)->toBe('Kompor surya mini')
        ->and($project->project_title)->toBe('Kompor surya mini');
});

function completeUnitForProjectSelectTest(User $student, LearningUnit $learningUnit): void
{
    foreach ($learningUnit->activities as $activity) {
        ActivityAnswer::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $student->id],
            ['answer_text' => 'Jawaban lengkap.', 'status' => 'submitted', 'submitted_at' => now()],
        );
    }

    foreach ($learningUnit->assessments as $assessment) {
        AssessmentAttempt::updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => $student->id, 'attempt_number' => 1],
            ['total_score' => 100, 'max_score' => 100, 'status' => 'tuntas', 'started_at' => now(), 'submitted_at' => now()],
        );
    }

    app(ProgressService::class)->refreshLearningUnitProgress($student, $learningUnit);
}
