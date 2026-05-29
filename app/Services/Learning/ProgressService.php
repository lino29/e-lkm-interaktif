<?php

namespace App\Services\Learning;

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\User;

class ProgressService
{
    public function markStarted(User $student, Module $module, ?LearningUnit $learningUnit = null): Progress
    {
        return Progress::updateOrCreate(
            [
                'user_id' => $student->id,
                'module_id' => $module->id,
                'learning_unit_id' => $learningUnit?->id,
            ],
            [
                'status' => 'sedang_dikerjakan',
                'started_at' => now(),
            ],
        );
    }

    public function recordAssessment(User $student, Assessment $assessment, float $score, string $status): Progress
    {
        return Progress::updateOrCreate(
            [
                'user_id' => $student->id,
                'module_id' => $assessment->module_id,
                'learning_unit_id' => $assessment->learning_unit_id,
                'assessment_id' => $assessment->id,
            ],
            [
                'status' => $status,
                'score' => $score,
                'completed_at' => $status === 'tuntas' ? now() : null,
            ],
        );
    }
}
