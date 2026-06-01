<?php

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RenewableEnergyActivitySeeder;

test('renewable energy activity seeder is idempotent and preserves answers', function () {
    $this->seed(DatabaseSeeder::class);

    $student = User::where('email', 'murid@elkm.test')->firstOrFail();
    $module = Module::where('slug', 'energi-terbarukan')->firstOrFail();
    $activity = Activity::whereIn('learning_unit_id', $module->learningUnits()->pluck('id'))
        ->where('phase', 'ayo_mengamati')
        ->firstOrFail();

    ActivityAnswer::create([
        'activity_id' => $activity->id,
        'user_id' => $student->id,
        'answer_text' => 'Jawaban lama tidak boleh hilang.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $activityCount = Activity::count();

    $this->seed(RenewableEnergyActivitySeeder::class);
    $this->seed(RenewableEnergyActivitySeeder::class);

    expect(Activity::count())->toBe($activityCount)
        ->and(ActivityAnswer::where('activity_id', $activity->id)->where('user_id', $student->id)->exists())->toBeTrue();
});
