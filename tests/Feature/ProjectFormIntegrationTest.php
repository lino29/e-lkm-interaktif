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

test('kb5 project form creates or updates project draft for the student', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $kb5 = LearningUnit::where('order', 5)->firstOrFail();

    LearningUnit::where('order', '<', 5)->get()->each(fn (LearningUnit $unit) => completeLearningUnitForProjectFormTest($student, $unit));

    $activity = Activity::where('learning_unit_id', $kb5->id)
        ->where('input_type', 'project_form')
        ->firstOrFail();

    Livewire::actingAs($student)
        ->test(ActivityPage::class, ['activity' => $activity->id])
        ->set('field_data.project_type', 'Audit Energi Kelas')
        ->set('field_data.problem', 'Lampu kelas sering menyala saat ruangan cukup terang.')
        ->set('field_data.objective', 'Mengurangi pemborosan listrik di kelas.')
        ->set('field_data.tools_materials', 'Lembar observasi, wattmeter sederhana, dan kamera.')
        ->set('field_data.procedure', 'Amati pemakaian lampu, catat waktu, dan bandingkan kebutuhan cahaya.')
        ->set('field_data.data_to_collect', 'Waktu pemakaian, jumlah lampu, dan kebiasaan pengguna kelas.')
        ->set('field_data.expected_result', 'Pemakaian listrik kelas lebih hemat.')
        ->call('submit')
        ->assertHasNoErrors();

    $project = Project::where('user_id', $student->id)
        ->where('learning_unit_id', $kb5->id)
        ->firstOrFail();

    expect($project->module_id)->toBe($kb5->module_id)
        ->and($project->project_type)->toBe('Audit Energi Kelas')
        ->and($project->data_to_collect)->toContain('Waktu pemakaian')
        ->and($project->status)->toBe('submitted');
});

function completeLearningUnitForProjectFormTest(User $student, LearningUnit $learningUnit): void
{
    foreach ($learningUnit->activities as $activity) {
        ActivityAnswer::updateOrCreate(
            [
                'activity_id' => $activity->id,
                'user_id' => $student->id,
            ],
            [
                'answer_text' => 'Jawaban lengkap untuk membuka kegiatan berikutnya.',
                'status' => 'submitted',
                'submitted_at' => now(),
            ],
        );
    }

    foreach ($learningUnit->assessments as $assessment) {
        AssessmentAttempt::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'attempt_number' => 1,
            ],
            [
                'total_score' => 100,
                'max_score' => 100,
                'status' => 'tuntas',
                'started_at' => now()->subMinute(),
                'submitted_at' => now(),
            ],
        );
    }

    app(ProgressService::class)->refreshLearningUnitProgress($student, $learningUnit);
}
